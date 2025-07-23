<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class CropProcessor extends AbstractPositionableProcessor
{
    use DimensionTrait;

    public function process(ImageInterface $image, Request $request): void
    {
        $crop = $this->validateDimensions($request, 'crop');

        if (
            null !== $crop
            && !$request->query->has('hotspot')
            && !$request->query->has('width')
            && !$request->query->has('height')
        ) {
            // Get width and height with ratio
            $size = $this->getCroppedWidthHeight($image->width(), $image->height(), $crop);

            $image->crop($size->getRoundedX(), $size->getRoundedY(), position: $this->parsePosition($request));
        }
    }
}
