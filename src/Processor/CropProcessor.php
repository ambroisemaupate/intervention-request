<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Vector;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class CropProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        $crop = CropProcessor::validateDimensions($request);
        if (
            null !== $crop
            && !$request->query->has('width')
            && !$request->query->has('height')
        ) {
            $image->crop($crop->getRoundedX(), $crop->getRoundedY());
        }
    }

    public static function validateDimensions(Request $request, string $paramName = 'crop'): ?Vector
    {
        $requestDimensions = $request->query->get($paramName);
        if (!is_string($requestDimensions)) {
            return null;
        }
        preg_match('#^([0-9]+)[x\:]([0-9]+)$#', $requestDimensions, $dimensions);

        if (isset($dimensions[1]) && isset($dimensions[2])) {
            return new Vector(
                $dimensions[1],
                $dimensions[2]
            );
        }

        return null;
    }
}
