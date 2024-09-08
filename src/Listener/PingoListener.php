<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final class PingoListener implements ImageFileEventSubscriberInterface
{
    protected string $pingoPath;
    protected bool $noAlpha = false;
    private bool $lossy = false;

    /**
     * @param string $pingoPath
     * @param bool $lossy
     * @param bool $noAlpha
     */
    public function __construct(string $pingoPath, bool $lossy = false, bool $noAlpha = false)
    {
        $this->pingoPath = $pingoPath;
        $this->noAlpha = $noAlpha;
        $this->lossy = $lossy;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onImageSaved',
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
            $this->pingoPath !== '' &&
            (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-Pingo' => '1']);
            $response->headers->add(['X-IR-Pingo-NoAlpha' => (int) $this->noAlpha]);
            $response->headers->add(['X-IR-Pingo-Lossy' => (int) $this->lossy]);
            $event->setResponse($response);
        }
    }

    /**
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null): bool
    {
        return $this->pingoPath !== '' &&
            null !== $image &&
            ($image->getMimeType() === 'image/png' || $image->getMimeType() === 'image/jpeg');
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
            $quality = $event->getQuality();
            $params = [
                'wine', // Pingo is WINDOWS only, requires Wine on your linux system.
                $this->pingoPath,
                '-strip',
                '-faster'
            ];
            switch ($event->getImageFile()->getMimeType()) {
                case 'image/png':
                    if ($this->lossy) {
                        $params[] = '-pngpalette=' . $quality;
                    } else {
                        $params[] = '-pngfilter=' . $quality;
                    }
                    if ($this->noAlpha) {
                        $params[] = '-noalpha';
                    }
                    break;
                case 'image/jpeg':
                    $params[] = '-jpgquality=' . $quality;
                    break;
            }
            $params[] = $event->getImageFile()->getPathname();
            $process = new Process($params);
            $process->run();
        }
    }
}
