<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Interfaces\ImageInterface;

final readonly class WatermarkListener implements ImageEventSubscriberInterface
{
    /**
     * @param string $watermarkPath The path string that will be use to search image for watermark
     * @param string $align         Position of the image to be placed, default: top-left
     * @param int    $offset_x      Optional relative offset of the new image on x-axis, default: 0
     * @param int    $offset_y      Optional relative offset of the new image on y-axis, default: 0
     * @param int    $opacity       Control over the opacity of the placed image ranging from 0 (fully transparent) to 100 (opaque), default: 100
     */
    public function __construct(
        private string $watermarkPath,
        private string $align = 'center',
        private int $offset_x = 0,
        private int $offset_y = 0,
        private int $opacity = 35,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImageAfterProcessEvent::class => 'watermarkImage',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ((bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Watermarked', '1');
            $event->setResponse($response);
        }
    }

    public function watermarkImage(ImageAfterProcessEvent $event): void
    {
        $image = $event->getImage();
        if (null !== $image && $this->supports($image)) {
            $image->place(
                $this->watermarkPath,
                $this->align,
                $this->offset_x,
                $this->offset_y,
                $this->opacity,
            );

            $event->setImage($image);
        }
    }

    public function supports(?ImageInterface $image = null): bool
    {
        return null !== $image;
    }
}
