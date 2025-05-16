<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Vector;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class CropResizedProcessor extends AbstractPositionableProcessor
{
    public function process(ImageInterface $image, Request $request): void
    {
        $crop = CropProcessor::validateDimensions($request);
        if (
            null !== $crop
            && !$request->query->has('hotspot')
            && ($request->query->has('width') || $request->query->has('height'))
        ) {
            $fitRatio = $crop->getX() / $crop->getY();

            if ($request->query->has('width')) {
                $realFitSize = new Vector(
                    (int) $request->query->get('width'),
                    round(floatval($request->query->get('width')) / $fitRatio)
                );
            } elseif ($request->query->has('height')) {
                $realFitSize = new Vector(
                    round(floatval($request->query->get('height')) * $fitRatio),
                    (int) $request->query->get('height'),
                );
            }

            if (isset($realFitSize)) {
                $image->coverDown($realFitSize->getRoundedX(), $realFitSize->getRoundedY(), $this->parsePosition($request));
            }
        }
    }
}
