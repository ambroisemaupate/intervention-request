<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final readonly class JpegTranListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $jpegtranPath)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onJpegImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (
            '' !== $this->jpegtranPath
            && 'image/jpeg' === $response->headers->get('Content-Type')
            && (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-JpegTran' => '1']);
            $event->setResponse($response);
        }
    }

    public function supports(?File $image = null): bool
    {
        return null !== $image && 'image/jpeg' === $image->getMimeType() && '' !== $this->jpegtranPath;
    }

    public function onJpegImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
            $process = new Process([
                $this->jpegtranPath,
                '-copy', 'none',
                '-optimize',
                '-progressive',
                '-outfile', $event->getImageFile()->getPathname(),
                $event->getImageFile()->getPathname(),
            ]);
            $process->run();
        }
    }
}
