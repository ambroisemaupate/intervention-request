<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final readonly class JpegFileListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $jpegoptimPath)
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
            '' !== $this->jpegoptimPath
            && 'image/jpeg' === $response->headers->get('Content-Type')
            && (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-JpegOptim' => 1]);
            $event->setResponse($response);
        }
    }

    public function supports(?Image $image = null, ?File $file = null): bool
    {
        return '' !== $this->jpegoptimPath && null !== $image && 'image/jpeg' === $image->mime();
    }

    public function onJpegImageSaved(ImageSavedEvent $event): void
    {
        if (!$this->supports($event->getImage(), $event->getImageFile())) {
            return;
        }

        $process = new Process([
            $this->jpegoptimPath,
            '-s',
            '-f',
            '--all-progressive',
            '-m90',
            $event->getImageFile()->getPathname(),
        ]);
        $process->run();
    }
}
