<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

/**
 * @package AM\InterventionRequest\Listener
 */
final class JpegFileListener implements ImageFileEventSubscriberInterface
{
    protected string $jpegoptimPath;

    /**
     * @param string $jpegoptimPath
     */
    public function __construct(string $jpegoptimPath)
    {
        $this->jpegoptimPath = $jpegoptimPath;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onJpegImageSaved',
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
            $this->jpegoptimPath !== '' &&
            $response->headers->get('Content-Type') === 'image/jpeg' &&
            (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-JpegOptim' => 1]);
            $event->setResponse($response);
        }
    }

    /**
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null): bool
    {
        return $this->jpegoptimPath !== '' && null !== $image && $image->getMimeType() === 'image/jpeg';
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onJpegImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
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
}
