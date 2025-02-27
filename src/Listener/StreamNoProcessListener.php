<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\FileResolverInterface;
use AM\InterventionRequest\FlysystemFileResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
 * Direct stream no-process files when FileResolver is a FlysystemFileResolver.
 */
final class StreamNoProcessListener implements EventSubscriberInterface
{
    public const ATTRIBUTE = 'stream_no_process';

    public function __construct(
        private readonly FileResolverInterface $fileResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 1000],
        ];
    }

    public function onRequest(RequestEvent $requestEvent): void
    {
        $hasNoProcess = $requestEvent->getRequest()->query->get('no_process', false);

        if (!$hasNoProcess) {
            return;
        }
        if (!$this->fileResolver instanceof FlysystemFileResolver) {
            return;
        }

        $filesystem = $this->fileResolver->getFilesystem();
        $nativeImage = $this->fileResolver->resolveFile(
            $this->fileResolver->assertRequestedFilePath($requestEvent->getRequest()->get('image'))
        );

        $requestEvent->getRequest()->attributes->set('no_process', true);
        $requestEvent->getRequest()->attributes->set(self::ATTRIBUTE, true);
        $response = new StreamedResponse(function () use ($nativeImage, $filesystem) {
            $outputStream = fopen('php://output', 'wb');
            if (false === $outputStream) {
                throw new \RuntimeException('Could not open php://output');
            }
            stream_copy_to_stream(
                $filesystem->readStream($nativeImage->getPathname()),
                $outputStream
            );
        }, Response::HTTP_OK, [
            'Content-Type' => $filesystem->mimeType($nativeImage->getPathname()),
            'Last-Modified' => $filesystem->lastModified($nativeImage->getPathname()),
            'Content-Disposition' => 'filename="'.$nativeImage->getFilename().'"',
        ]);
        $response->setPublic();
        $requestEvent->setResponse($response);
    }
}
