<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
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

    public function supports(?Image $image = null, ?File $file = null): bool
    {
        return null !== $image && 'image/jpeg' === $image->mime() && '' !== $this->jpegtranPath;
    }

    public function onJpegImageSaved(ImageSavedEvent $event): void
    {
        if (!$this->supports($event->getImage(), $event->getImageFile())) {
            return;
        }

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
