<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final readonly class PngquantListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $pngquantPath, private bool $lossy = false)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onPngImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (
            '' !== $this->pngquantPath
            && 'image/png' === $response->headers->get('Content-Type')
            && (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-Pngquant' => '1']);
            $response->headers->add(['X-IR-Pngquant-Lossy' => (int) $this->lossy]);
            $event->setResponse($response);
        }
    }

    public function supports(?Image $image = null, ?File $file = null): bool
    {
        return '' !== $this->pngquantPath && null !== $image && 'image/png' === $image->mime();
    }

    public function onPngImageSaved(ImageSavedEvent $event): void
    {
        if (!$this->supports($event->getImage(), $event->getImageFile())) {
            return;
        }

        $maxQuality = $event->getQuality();
        $minQuality = $maxQuality - 10;
        if ($maxQuality > 100) {
            $maxQuality = 100;
        }
        if ($minQuality < 0) {
            $minQuality = 0;
        }
        $process = new Process([
            $this->pngquantPath,
            '-f',
            '--speed',
            '2',
            $this->lossy ? '--quality='.sprintf('%d-%d', $minQuality, $maxQuality) : '',
            '-o',
            $event->getImageFile()->getPathname(),
            $event->getImageFile()->getPathname(),
        ]);
        $process->run();
    }
}
