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
final class JpegTranListener implements ImageFileEventSubscriberInterface
{
    protected string $jpegtranPath;

    /**
     * @param string $jpegtranPath
     */
    public function __construct(string $jpegtranPath)
    {
        $this->jpegtranPath = $jpegtranPath;
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
            $this->jpegtranPath !== '' &&
            $response->headers->get('Content-Type') === 'image/jpeg' &&
            (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-JpegTran' => '1']);
            $event->setResponse($response);
        }
    }

    /**
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null): bool
    {
        return null !== $image && $image->getMimeType() === 'image/jpeg' && $this->jpegtranPath !== '';
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
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
