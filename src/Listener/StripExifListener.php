<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use Intervention\Image\Drivers\Imagick\Core as ImagickCore;
use Intervention\Image\Drivers\Imagick\Modifiers\StripMetaModifier;
use Intervention\Image\Interfaces\ImageInterface;

final class StripExifListener implements ImageEventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ImageAfterProcessEvent::class => 'afterProcess',
        ];
    }

    public function supports(?ImageInterface $image = null): bool
    {
        return null !== $image && class_exists('\Imagick') && $image->core() instanceof ImagickCore;
    }

    public function afterProcess(ImageAfterProcessEvent $afterProcessEvent): void
    {
        if (
            null !== $afterProcessEvent->getImage()
            && $this->supports($afterProcessEvent->getImage())
        ) {
            $modifier = new StripMetaModifier();
            $modifier->apply($afterProcessEvent->getImage());
        }
    }
}
