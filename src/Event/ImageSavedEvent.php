<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use Intervention\Image\Image;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Event dispatched AFTER an image has been saved to filesystem.
 */
class ImageSavedEvent extends ImageEvent
{
    public function __construct(?ImageInterface $image, protected readonly File $imageFile, protected int $quality = 90)
    {
        parent::__construct($image);
    }

    public function getImageFile(): File
    {
        return $this->imageFile;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): ImageSavedEvent
    {
        $this->quality = $quality;

        return $this;
    }
}
