<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final readonly class OxipngListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $oxipngPath)
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
        $contentType = $response->headers->get('Content-Type', '');
        if (
            $this->oxipngPath !== '' &&
            null !== $contentType &&
            strtolower($contentType) === 'image/png' &&
            (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-Oxipng' => '1']);
            $event->setResponse($response);
        }
    }

    public function supports(?File $image = null): bool
    {
        return $this->oxipngPath !== '' &&
            null !== $image &&
            null !== $image->getMimeType() &&
            strtolower($image->getMimeType()) === 'image/png';
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onPngImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
            $process = new Process([
                $this->oxipngPath,
                '-o',
                '4',
                '--strip',
                'safe',
                '--out',
                $event->getImageFile()->getPathname(),
                $event->getImageFile()->getPathname(),
            ]);
            $process->run();
        }
    }
}
