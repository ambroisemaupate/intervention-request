<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface ImageEventSubscriberInterface extends EventSubscriberInterface
{
    /**
     * Return true if current subscriber can be used for current image.
     */
    public function supports(?ImageInterface $image = null): bool;
}
