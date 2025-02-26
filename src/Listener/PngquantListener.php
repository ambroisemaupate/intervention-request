<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final readonly class PngquantListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $pngquantPath, private bool $lossy = false)
    {
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onPngImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (
            $this->pngquantPath !== '' &&
            $response->headers->get('Content-Type') === 'image/png' &&
            (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-Pngquant' => '1']);
            $response->headers->add(['X-IR-Pngquant-Lossy' => (int) $this->lossy]);
            $event->setResponse($response);
        }
    }

    public function supports(?File $image = null): bool
    {
        return $this->pngquantPath !== '' && null !== $image && $image->getMimeType() === 'image/png';
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onPngImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
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
                $this->lossy ? '--quality=' . sprintf('%d-%d', $minQuality, $maxQuality) : '',
                '-o',
                $event->getImageFile()->getPathname(),
                $event->getImageFile()->getPathname(),
            ]);
            $process->run();
        }
    }
}
