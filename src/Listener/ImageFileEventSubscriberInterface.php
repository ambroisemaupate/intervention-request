<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\File;

interface ImageFileEventSubscriberInterface extends EventSubscriberInterface
{
    /**
     * Return true if current subscriber can be used for current image.
     *
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null): bool;
}
