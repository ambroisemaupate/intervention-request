<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Encoder;

use Intervention\Image\Exceptions\NotWritableException;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;

class ImageEncoder
{
    /**
     * @var array<string>
     */
    public static array $allowedExtensions = [
        'jpeg', 'jpg', 'gif', 'png', 'webp', 'avif', 'tiff', 'tif', 'bmp', 'svg', 'ico',
    ];

    public function encode(ImageInterface $image, string $path, int $quality, bool $progressive = false): EncodedImageInterface
    {
        return $image->encodeByExtension($this->getImageAllowedExtension($path), quality: $quality, progressive: $progressive);
    }

    public function save(ImageInterface $image, string $path, int $quality, bool $progressive = false): ImageInterface
    {
        $path = empty($path) ? $image->origin()->filePath() : $path;

        if (empty($path)) {
            throw new NotWritableException("Can't write to undefined path.");
        }

        $data = $this->encode($image, $path, $quality, $progressive);
        $saved = @file_put_contents($path, $data);

        if (false === $saved) {
            throw new NotWritableException("Can't write image data to path ({$path})");
        }

        return $image;
    }

    public function getImageAllowedExtension(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, static::$allowedExtensions)) {
            return 'jpg';
        }

        return $extension;
    }
}
