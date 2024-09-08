<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use Intervention\Image\Image;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package AM\InterventionRequest\Event
 */
abstract class ImageEvent extends Event
{
    protected ?Image $image;

    /**
     * @param Image|null $image
     */
    public function __construct(?Image $image = null)
    {
        $this->image = $image;
    }

    /**
     * @return Image|null
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * @param Image $image
     * @return ImageEvent
     */
    public function setImage(Image $image): ImageEvent
    {
        $this->image = $image;
        return $this;
    }
}
