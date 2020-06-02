<?php
declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use Intervention\Image\Image;

final class StripExifListener implements ImageEventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ImageAfterProcessEvent::class => 'afterProcess'
        ];
    }

    /**
     * @param Image|null $image
     *
     * @return bool
     */
    public function supports(Image $image = null)
    {
        return null !== $image && class_exists('\Imagick') && $image->getCore() instanceof \Imagick;
    }

    /**
     * @param ImageAfterProcessEvent $afterProcessEvent
     * @return void
     */
    public function afterProcess(ImageAfterProcessEvent $afterProcessEvent)
    {
        if (null !== $afterProcessEvent->getImage() && $this->supports($afterProcessEvent->getImage())) {
            $afterProcessEvent->getImage()->getCore()->stripImage();
        }
    }
}
