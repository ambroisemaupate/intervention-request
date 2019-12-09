<?php

namespace AM\InterventionRequest\Encoder;

use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image;

class ImageEncoder
{
    /**
     * @var array<string>
     */
    public static $allowedExtensions = [
        'jpeg', 'jpg', 'gif', 'png', 'webp', 'tiff', 'tif', 'bmp', 'svg', 'ico'
    ];

    /**
     * @param Image $image
     * @param string $path
     * @param int $quality
     * @return Image
     */
    public function encode(Image $image, $path, $quality)
    {
        return $image->encode($this->getImageAllowedExtension($path), $quality);
    }

    /**
     * @param Image $image
     * @param string $path
     * @param int $quality
     * @return Image
     */
    public function save(Image $image, $path, $quality)
    {
        $path = is_null($path) ? $image->basePath() : $path;

        if (is_null($path)) {
            throw new NotWritableException(
                "Can't write to undefined path."
            );
        }

        $data = $this->encode($image, $path, $quality);
        $saved = @file_put_contents($path, $data);

        if ($saved === false) {
            throw new NotWritableException(
                "Can't write image data to path ({$path})"
            );
        }

        // set new file info
        $image->setFileInfoFromPath($path);

        return $image;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getImageAllowedExtension($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, static::$allowedExtensions)) {
            return 'jpg';
        }

        return $extension;
    }
}
