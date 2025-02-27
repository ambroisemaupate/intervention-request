<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final readonly class PingoListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $pingoPath, private bool $lossy = false, private bool $noAlpha = false)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (
            '' !== $this->pingoPath
            && (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-Pingo' => '1']);
            $response->headers->add(['X-IR-Pingo-NoAlpha' => (int) $this->noAlpha]);
            $response->headers->add(['X-IR-Pingo-Lossy' => (int) $this->lossy]);
            $event->setResponse($response);
        }
    }

    public function supports(?Image $image = null, ?File $file = null): bool
    {
        return '' !== $this->pingoPath
            && null !== $image
            && ('image/png' === $image->mime() || 'image/jpeg' === $image->mime());
    }

    public function onImageSaved(ImageSavedEvent $event): void
    {
        if (!$this->supports($event->getImage(), $event->getImageFile())) {
            return;
        }

        $quality = $event->getQuality();
        $params = [
            'wine', // Pingo is WINDOWS only, requires Wine on your linux system.
            $this->pingoPath,
            '-strip',
            '-faster',
        ];
        switch ($event->getImageFile()->getMimeType()) {
            case 'image/png':
                if ($this->lossy) {
                    $params[] = '-pngpalette='.$quality;
                } else {
                    $params[] = '-pngfilter='.$quality;
                }
                if ($this->noAlpha) {
                    $params[] = '-noalpha';
                }
                break;
            case 'image/jpeg':
                $params[] = '-jpgquality='.$quality;
                break;
        }
        $params[] = $event->getImageFile()->getPathname();
        $process = new Process($params);
        $process->run();
    }
}
