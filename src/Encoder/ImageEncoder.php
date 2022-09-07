<?php

namespace AM\InterventionRequest\Encoder;

use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image;

class ImageEncoder
{
    /**
     * @var array<string>
     */
    public static array $allowedExtensions = [
        'jpeg', 'jpg', 'gif', 'png', 'webp', 'avif', 'tiff', 'tif', 'bmp', 'svg', 'ico'
    ];

    /**
     * @param Image $image
     * @param string $path
     * @param int $quality
     * @return Image
     */
    public function encode(Image $image, string $path, int $quality): Image
    {
        return $image->encode($this->getImageAllowedExtension($path), $quality);
    }

    /**
     * @param Image $image
     * @param string $path
     * @param int $quality
     * @return Image
     */
    public function save(Image $image, string $path, int $quality): Image
    {
        $path = empty($path) ? $image->basePath() : $path;

        if (empty($path)) {
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
    public function getImageAllowedExtension(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, static::$allowedExtensions)) {
            return 'jpg';
        }

        return $extension;
    }
}
