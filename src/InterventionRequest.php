<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use AM\InterventionRequest\Cache\FileCache;
use AM\InterventionRequest\Cache\PassThroughFileCache;
use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use AM\InterventionRequest\Listener\JpegFileListener;
use AM\InterventionRequest\Listener\NoCacheImageRequestSubscriber;
use AM\InterventionRequest\Listener\OxipngListener;
use AM\InterventionRequest\Listener\PingoListener;
use AM\InterventionRequest\Listener\PngquantListener;
use AM\InterventionRequest\Listener\QualitySubscriber;
use AM\InterventionRequest\Listener\StreamNoProcessListener;
use AM\InterventionRequest\Listener\StripExifListener;
use Intervention\Image\Exception\NotReadableException;
use League\Flysystem\UnableToRetrieveMetadata;
use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InterventionRequest
{
    protected ?Response $response = null;
    protected EventDispatcherInterface $dispatcher;

    /**
     * @param Processor\Processor[]|null $processors
     */
    public function __construct(
        protected readonly Configuration $configuration,
        protected readonly FileResolverInterface $fileResolver,
        protected readonly LoggerInterface $logger,
        ?array $processors = null,
        protected bool $debug = false,
    ) {
        if ($this->debug) {
            Debug::enable();
        }
        $this->dispatcher = new EventDispatcher();
        $chainProcessor = $this->getChainProcessor($processors);

        if (null !== $this->configuration->getPingoPath()) {
            // Pingo replaces jpeg and png optimizers
            $this->addSubscriber(new PingoListener(
                $this->configuration->getPingoPath(),
                $this->configuration->isLossyPng(),
                $this->configuration->isNoAlphaPingo()
            ));
        } else {
            if (null !== $this->configuration->getJpegoptimPath()) {
                $this->addSubscriber(new JpegFileListener($this->configuration->getJpegoptimPath()));
            }
            if (null !== $this->configuration->getOxipngPath()) {
                $this->addSubscriber(new OxipngListener($this->configuration->getOxipngPath()));
            } elseif (null !== $this->configuration->getPngquantPath()) {
                $this->addSubscriber(new PngquantListener(
                    $this->configuration->getPngquantPath(),
                    $this->configuration->isLossyPng()
                ));
            }
        }

        $this->addSubscriber(new StreamNoProcessListener(
            $this->fileResolver,
        ));
        $this->addSubscriber(new StripExifListener());
        $this->addSubscriber(new QualitySubscriber($this->configuration->getDefaultQuality()));
        $this->addSubscriber(new FileCache(
            $chainProcessor,
            $this->fileResolver,
            $this->configuration->getCachePath(),
            $this->logger,
            $this->configuration->getTtl(),
            $this->configuration->getGcProbability(),
            $this->configuration->getUseFileChecksum()
        ));
        $this->addSubscriber(new PassThroughFileCache(
            $chainProcessor,
            $this->fileResolver,
            $this->configuration->getCachePath(),
            $this->logger,
            $this->configuration->getTtl(),
            $this->configuration->getGcProbability(),
            $this->configuration->getUseFileChecksum()
        ));
        $this->addSubscriber(new NoCacheImageRequestSubscriber(
            $chainProcessor,
            $this->fileResolver
        ));

        $this->defineTimezone();
    }

    private function defineTimezone(): void
    {
        /*
         * Define a request wide timezone
         */
        date_default_timezone_set($this->configuration->getTimezone());
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @param Processor\Processor[]|null $processors
     */
    protected function getChainProcessor(?array $processors = null): Processor\ChainProcessor
    {
        return new Processor\ChainProcessor(
            $this->configuration,
            $this->dispatcher,
            $processors ?? [
                new Processor\RotateProcessor(),
                new Processor\FlipProcessor(),
                new Processor\CropResizedProcessor(),
                new Processor\FitProcessor(),
                new Processor\CropProcessor(),
                new Processor\WidenProcessor(),
                new Processor\HeightenProcessor(),
                new Processor\LimitColorsProcessor(),
                new Processor\GreyscaleProcessor(),
                new Processor\ContrastProcessor(),
                new Processor\BlurProcessor(),
                new Processor\SharpenProcessor(),
                new Processor\ProgressiveProcessor(),
            ]
        );
    }

    /**
     * Handle request to convert it to a Response object.
     *
     * @throws \Exception
     */
    public function handleRequest(Request $request): void
    {
        try {
            if (!$request->query->has('image')) {
                throw new \InvalidArgumentException('No valid image path found in URI');
            }

            $event = new RequestEvent($request, $this);
            $this->dispatcher->dispatch($event);
            if (null === $this->response = $event->getResponse()) {
                throw new \LogicException('No listener returned a Response for current request');
            }
        } catch (FileNotFoundException|UnableToRetrieveMetadata $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->response = $this->getNotFoundResponse($e);
        } catch (\InvalidArgumentException|NotReadableException $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->response = $this->getBadRequestResponse($e);
        } catch (\Throwable $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->response = $this->getServerErrorResponse($e);
        }
    }

    protected function getNotFoundResponse(\Throwable $e): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => array_filter([
                    'code' => Response::HTTP_NOT_FOUND,
                    'exception' => $this->debug ? $e::class : null,
                    'message' => $e->getMessage(),
                ]),
            ],
            Response::HTTP_NOT_FOUND,
            ['cache-control' => 'no-store']
        );
    }

    protected function getBadRequestResponse(\Throwable $e): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => array_filter([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'exception' => $this->debug ? $e::class : null,
                    'message' => $e->getMessage(),
                ]),
            ],
            Response::HTTP_BAD_REQUEST,
            ['cache-control' => 'no-store']
        );
    }

    protected function getServerErrorResponse(\Throwable $e): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => array_filter([
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'exception' => $this->debug ? $e::class : null,
                    'message' => $e->getMessage(),
                ]),
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR,
            ['cache-control' => 'no-store']
        );
    }

    public function getResponse(Request $request): Response
    {
        if (null === $this->response) {
            throw new \RuntimeException('Request had not been handled. Use handle() method before getResponse()', 1);
        }

        if ($this->response->isCacheable()) {
            $this->response->setPublic();
            $this->response->setMaxAge($this->configuration->getResponseTtl());
            $this->response->setSharedMaxAge($this->configuration->getResponseTtl());
        }
        $this->response->setCharset('UTF-8');
        $this->response->headers->set(
            'access-control-allow-headers',
            'DNT,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range'
        );
        $this->response->headers->set(
            'access-control-allow-methods',
            'GET, OPTIONS'
        );
        $this->response->headers->set(
            'access-control-allow-origin',
            '*'
        );
        $responseEvent = new ResponseEvent($this->response);
        $this->dispatcher->dispatch($responseEvent);
        $this->response = $responseEvent->getResponse();
        $this->response->prepare($request);

        return $this->response;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }
}
