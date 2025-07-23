<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Encoder;

use Intervention\Image\Exceptions\NotWritableException;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final class ImageEncoder implements ImageEncoderInterface
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
        $filesystem = new Filesystem();
        $path = empty($path) ? $image->origin()->filePath() : $path;

        if (empty($path)) {
            throw new NotWritableException("Can't write to undefined path.");
        }

        $data = $this->encode($image, $path, $quality, $progressive);

        try {
            $filesystem->dumpFile($path, $data->toFilePointer());
        } catch (IOException $e) {
            throw new NotWritableException("Can't write image data to path ({$path}): ".$e->getMessage(), previous: $e);
        }

        return $image;
    }

    private function getImageAllowedExtension(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            return 'jpg';
        }

        return $extension;
    }
}
