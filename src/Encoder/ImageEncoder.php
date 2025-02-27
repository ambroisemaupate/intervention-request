<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Encoder;

use AM\InterventionRequest\FileResolverInterface;
use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image;

final class ImageEncoder
{
    public function __construct(private readonly FileResolverInterface $fileResolver)
    {
    }

    /**
     * @var array<string>
     */
    public static array $allowedExtensions = [
        'jpeg', 'jpg', 'gif', 'png', 'webp', 'avif', 'tiff', 'tif', 'bmp', 'svg', 'ico',
    ];

    public function encode(Image $image, string $path, int $quality): Image
    {
        return $image->encode($this->getImageAllowedExtension($path), $quality);
    }

    public function save(Image $image, string $path, int $quality): Image
    {
        if (empty($path)) {
            throw new NotWritableException("Can't write to undefined path.");
        }

        $data = $this->encode($image, $path, $quality);

        return $this->fileResolver->saveImageData($data, $path);
    }

    public function getImageAllowedExtension(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            return 'jpg';
        }

        return $extension;
    }
}
