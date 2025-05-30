<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Encoder\ImageEncoderInterface;
use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\FileResolverInterface;
use AM\InterventionRequest\Processor\ChainProcessor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class NoCacheImageRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChainProcessor $processor,
        private FileResolverInterface $fileResolver,
        private ImageEncoderInterface $imageEncoder,
    ) {
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 0],
        ];
    }

    public function onRequest(RequestEvent $requestEvent): void
    {
        if (false === $requestEvent->getInterventionRequest()->getConfiguration()->hasCaching()) {
            $request = $requestEvent->getRequest();
            $nativeImage = $this->fileResolver->resolveFile(
                $this->fileResolver->assertRequestedFilePath($request->get('image'))
            );
            $image = $this->processor->process($nativeImage, $request);

            if ($nativeImage->isNextGen()) {
                $response = new Response(
                    $this->imageEncoder->encode($image, $nativeImage->getPath(), $requestEvent->getQuality(), $requestEvent->isProgressive())->toString(),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => $nativeImage->getNextGenMimeType(),
                        'Content-Disposition' => 'filename="'.$nativeImage->getRequestedFile()->getFilename().'"',
                        'X-IR-Cached' => '0',
                        'X-IR-First-Gen' => '1',
                    ]
                );
            } else {
                $encodedImage = $this->imageEncoder->encode($image, $image->origin()->filePath() ?? '', $requestEvent->getQuality(), $requestEvent->isProgressive());
                $response = new Response(
                    $encodedImage->toString(),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => $encodedImage->mimetype(),
                        'Content-Disposition' => 'filename="'.$nativeImage->getFilename().'"',
                        'X-IR-Cached' => '0',
                        'X-IR-First-Gen' => '1',
                    ]
                );
            }
            $response->setLastModified(new \DateTime('now'));
            $requestEvent->setResponse($response);
        }
    }
}
