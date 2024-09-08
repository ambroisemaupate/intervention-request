<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Event dispatched AFTER an image has been saved to filesystem.
 *
 * @package AM\InterventionRequest\Event
 */
class ImageSavedEvent extends ImageEvent
{
    /**
     * @deprecated Use ImageSavedEvent::class
     */
    public const NAME = ImageSavedEvent::class;

    protected File $imageFile;
    protected int $quality;

    /**
     * @param Image|null $image
     * @param File $imageFile
     * @param int $quality
     */
    public function __construct(?Image $image, File $imageFile, int $quality = 90)
    {
        parent::__construct($image);
        $this->imageFile = $imageFile;
        $this->quality = $quality;
    }

    /**
     * @return File
     */
    public function getImageFile(): File
    {
        return $this->imageFile;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     * @return ImageSavedEvent
     */
    public function setQuality(int $quality): ImageSavedEvent
    {
        $this->quality = $quality;
        return $this;
    }
}
