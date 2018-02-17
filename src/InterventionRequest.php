<?php
/**
 * Copyright © 2016, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file InterventionRequest.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest;

use AM\InterventionRequest\Cache\FileCache;
use AM\InterventionRequest\Cache\PassThroughFileCache;
use AM\InterventionRequest\Event\ImageProcessEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use AM\InterventionRequest\Listener\JpegFileListener;
use AM\InterventionRequest\Listener\PngFileListener;
use AM\InterventionRequest\Processor as Processor;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InterventionRequest
 * @package AM\InterventionRequest
 */
class InterventionRequest
{
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var null|LoggerInterface
     */
    protected $logger;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var File
     */
    protected $nativeImage;
    /**
     * @var Image
     */
    protected $image;
    /**
     * @var array|null
     */
    protected $processors;
    /**
     * @var integer
     */
    protected $quality;
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Create a new InterventionRequest object.
     *
     * @param Configuration        $configuration
     * @param LoggerInterface|null $logger
     * @param array|null           $processors
     */
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger = null,
        array $processors = null
    ) {
        $this->logger = $logger;
        $this->dispatcher = new EventDispatcher();
        $this->configuration = $configuration;

        if ($this->configuration->getJpegoptimPath() != '') {
            $this->dispatcher->addSubscriber(new JpegFileListener($this->configuration->getJpegoptimPath()));
        }

        if ($this->configuration->getPngquantPath() != '') {
            $this->dispatcher->addSubscriber(new PngFileListener($this->configuration->getPngquantPath()));
        }

        $this->defineTimezone();

        if (null === $processors) {
            $this->processors = [
                new Processor\RotateProcessor(),
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
            ];
        } elseif (is_array($processors)) {
            $this->processors = $processors;
        }
    }


    private function defineTimezone()
    {
        /*
         * Define a request wide timezone
         */
        date_default_timezone_set($this->configuration->getTimezone());
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @param Request $request
     * @return FileCache|PassThroughFileCache
     */
    protected function getCache(Request $request)
    {
        if ($this->configuration->isUsingPassThroughCache()) {
            $cache = new PassThroughFileCache(
                $request,
                $this->nativeImage,
                $this->configuration->getCachePath(),
                $this->logger,
                $this->quality,
                $this->configuration->getTtl(),
                $this->configuration->getGcProbability(),
                $this->configuration->getUseFileChecksum()
            );
        } else {
            $cache = new FileCache(
                $request,
                $this->nativeImage,
                $this->configuration->getCachePath(),
                $this->logger,
                $this->quality,
                $this->configuration->getTtl(),
                $this->configuration->getGcProbability(),
                $this->configuration->getUseFileChecksum()
            );
        }
        $cache->setDispatcher($this->dispatcher);
        return $cache;
    }

    /**
     * Handle request to convert it to a Response object.
     * @param Request $request
     */
    public function handleRequest(Request $request)
    {
        try {
            if (!$request->query->has('image')) {
                throw new FileNotFoundException("No valid image path found in URI");
            }

            $nativePath = $this->configuration->getImagesPath() . '/' . $request->query->get('image');
            $this->nativeImage = new File($nativePath);
            $this->parseQuality($request);

            if ($this->configuration->hasCaching()) {
                $cache = $this->getCache($request);

                /** @var Response response */
                $this->response = $cache->getResponse(function (InterventionRequest $interventionRequest) use ($request) {
                    return $interventionRequest->processImage($request);
                }, $this);
            } else {
                $this->processImage($request);
                $this->response = new Response(
                    (string) $this->image->encode(null, $this->quality),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => $this->image->mime(),
                        'Content-Disposition' => 'filename="' . $this->nativeImage->getFilename() . '"',
                        'X-Generator-No-Cache' => true,
                    ]
                );
                $this->response->setLastModified(new \DateTime('now'));
            }
        } catch (FileNotFoundException $e) {
            $this->response = $this->getNotFoundResponse($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->response = $this->getBadRequestResponse($e->getMessage());
        }
    }

    /**
     * @param string $message
     * @return Response
     */
    protected function getNotFoundResponse($message = "")
    {
        $body = '<h1>404 Error: File not found</h1>';
        if ($message != '') {
            $body .= '<p>' . $message . '</p>';
        }
        $body = '<!DOCTYPE html><html><body>' . $body . '</body></html>';

        return new Response(
            $body,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param string $message
     * @return Response
     */
    protected function getBadRequestResponse($message = "")
    {
        $body = '<h1>400 Error: Bad Request</h1>';
        if ($message != '') {
            $body .= '<p>' . $message . '</p>';
        }
        $body = '<!DOCTYPE html><html><body>' . $body . '</body></html>';

        return new Response(
            $body,
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param Request $request
     * @return Image
     */
    public function processImage(Request $request)
    {
        // create an image manager instance with favored driver
        $manager = new ImageManager([
            'driver' => $this->configuration->getDriver(),
        ]);

        $beforeProcessEvent = new ImageProcessEvent($manager->make($this->nativeImage->getPathname()));
        $this->dispatcher->dispatch(ImageProcessEvent::BEFORE_PROCESS, $beforeProcessEvent);

        /*
         * Get image altered by BEFORE subscribers
         */
        $this->image = $beforeProcessEvent->getImage();

        foreach ($this->processors as $processor) {
            $processor->process($this->image, $request);
        }

        $afterProcessEvent = new ImageProcessEvent($this->image);
        $this->dispatcher->dispatch(ImageProcessEvent::AFTER_PROCESS, $afterProcessEvent);

        /*
         * Get image altered by AFTER subscribers
         */
        return $afterProcessEvent->getImage();
    }

    /**
     * @param Request $request
     * @return int|mixed
     */
    public function parseQuality(Request $request)
    {
        if ($request->query->has('quality')) {
            $quality = (int) $request->query->get('quality');

            if ($quality <= 100 &&
                $quality > 0) {
                $this->quality = $quality;
            } else {
                $this->quality = $this->configuration->getDefaultQuality();
            }
        } else {
            $this->quality = $this->configuration->getDefaultQuality();
        }

        return $this->quality;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getResponse(Request $request)
    {
        if (null !== $this->response) {
            $this->response->setPublic();
            $this->response->setPrivate();
            $this->response->setMaxAge($this->configuration->getTtl());
            $this->response->setSharedMaxAge($this->configuration->getTtl());
            $this->response->setCharset('UTF-8');

            $responseEvent = new ResponseEvent($this->response, $this->image);
            $this->dispatcher->dispatch(ResponseEvent::NAME, $responseEvent);
            $this->response = $responseEvent->getResponse();

            $this->response->prepare($request);

            return $this->response;
        } else {
            throw new \RuntimeException("Request had not been handled. Use handle() method before getResponse()", 1);
        }
    }

    /**
     * @return File
     */
    public function getNativeImage()
    {
        return $this->nativeImage;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return null|LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
