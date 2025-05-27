<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class ImageEvent extends Event
{
    public function __construct(protected ?ImageInterface $image = null)
    {
    }

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(ImageInterface $image): ImageEvent
    {
        $this->image = $image;

        return $this;
    }
}
