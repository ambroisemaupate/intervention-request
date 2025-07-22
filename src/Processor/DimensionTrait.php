<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Vector;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

trait DimensionTrait
{
    private function getCroppedWidthHeight(ImageInterface $image, Vector $crop): Vector
    {
        $cropX = $crop->getRoundedX();
        $cropY = $crop->getRoundedY();
        // Square ratio
        if ($cropX == $cropY) {
            $width = $height = min($image->width(), $image->height());
        } elseif ($cropX > $cropY) { // Horizontal ratio
            $width = $image->width();
            $height = (int) round(($image->width() * $cropY) / $cropX);
        } else { // Vertical ratio
            $width = (int) round(($image->height() * $cropX) / $cropY);
            $height = $image->height();
        }

        return new Vector(
            $width,
            $height
        );
    }

    public function validateDimensions(Request $request, string $paramName): ?Vector
    {
        $requestDimensions = $request->query->get($paramName);
        if (!is_string($requestDimensions)) {
            return null;
        }

        if (1 === preg_match('#^([0-9]+)[Xx\:]([0-9]+)$#', $requestDimensions, $dimensions)) {
            return new Vector(
                $dimensions[1],
                $dimensions[2]
            );
        }

        return null;
    }

    public function validateNormalizedVector(Request $request, string $paramName): ?Vector
    {
        $data = $request->query->get($paramName);
        if (!is_string($data)) {
            return null;
        }

        if (1 === preg_match('#^(0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)$#', $data, $hotspot)) {
            return new Vector(
                $hotspot[1],
                $hotspot[2]
            );
        }

        return null;
    }
}
