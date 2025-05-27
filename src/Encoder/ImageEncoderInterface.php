<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Encoder;

use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;

interface ImageEncoderInterface
{
    public function encode(ImageInterface $image, string $path, int $quality, bool $progressive = false): EncodedImageInterface;

    public function save(ImageInterface $image, string $path, int $quality, bool $progressive = false): ImageInterface;
}
