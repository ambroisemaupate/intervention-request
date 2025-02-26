<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use Intervention\Image\Image;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface ImageEventSubscriberInterface extends EventSubscriberInterface
{
    /**
     * Return true if current subscriber can be used for current image.
     *
     * @param Image|null $image
     * @return bool
     */
    public function supports(?Image $image = null): bool;
}
