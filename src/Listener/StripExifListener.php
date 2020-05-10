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

    public function supports(Image $image = null)
    {
        return null !== $image && $image->getCore() instanceof \Imagick;
    }

    public function afterProcess(ImageAfterProcessEvent $afterProcessEvent)
    {
        if ($this->supports($afterProcessEvent->getImage())) {
            $afterProcessEvent->getImage()->getCore()->stripImage();
        }
    }
}
