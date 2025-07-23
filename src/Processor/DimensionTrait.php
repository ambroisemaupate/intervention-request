<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Hotspot;
use AM\InterventionRequest\Vector;
use Symfony\Component\HttpFoundation\Request;

trait DimensionTrait
{
    private function getCroppedWidthHeight(int $imageWidth, int $imageHeight, Vector $crop): Vector
    {
        if ($crop->getX() == $crop->getY()) {
            // If the crop ratio is square, we need to use the smallest dimension
            $width = $height = min($imageWidth, $imageHeight);
        } elseif ($crop->getX() / $crop->getY() > $imageWidth / $imageHeight) {
            // If the crop ratio is wider than the image ratio
            // we need to adjust the height
            $width = $imageWidth;
            $height = ($imageWidth * $crop->getY()) / $crop->getX();
        } else {
            // If the crop ratio is taller than the image ratio
            // we need to adjust the width
            $width = ($imageHeight * $crop->getX()) / $crop->getY();
            $height = $imageHeight;
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

    public function validateNormalizedHotspot(Request $request, string $paramName): ?Hotspot
    {
        $data = $request->query->get($paramName);
        if (!is_string($data)) {
            return null;
        }

        if (1 === preg_match('#^(0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)$#', $data, $hotspot)) {
            return Hotspot::point(
                new Vector($hotspot[1], $hotspot[2])
            );
        }

        if (1 === preg_match('#^(0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)$#', $data, $hotspot)) {
            return new Hotspot(
                new Vector($hotspot[1], $hotspot[2]),
                new Vector($hotspot[3], $hotspot[4]),
                new Vector($hotspot[5], $hotspot[6]),
            );
        }

        return null;
    }
}
