<?php
/**
 * Copyright © 2016, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file ImageSavedEvent.php
 * @author Ambroise Maupate
 */
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
    const NAME = 'image.saved';

    /**
     * @var File
     */
    protected $imageFile;

    /**
     * ImageSavedEvent constructor.
     * @param Image $image
     * @param File $imageFile
     */
    public function __construct(Image $image, File $imageFile)
    {
        parent::__construct($image);
        $this->imageFile = $imageFile;
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }
}
