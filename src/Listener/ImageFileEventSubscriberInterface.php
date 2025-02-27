<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use Intervention\Image\Image;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\File;

interface ImageFileEventSubscriberInterface extends EventSubscriberInterface
{
    /**
     * Return true if current subscriber can be used for current image.
     */
    public function supports(?Image $image = null, ?File $file = null): bool;
}
