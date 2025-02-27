<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use Intervention\Image\Image;
use Symfony\Contracts\EventDispatcher\Event;

abstract class ImageEvent extends Event
{
    public function __construct(protected ?Image $image = null)
    {
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(Image $image): ImageEvent
    {
        $this->image = $image;

        return $this;
    }
}
