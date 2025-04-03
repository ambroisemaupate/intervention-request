<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Vector;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class CropResizedProcessor extends AbstractPositionableProcessor
{
    public function process(Image $image, Request $request): void
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
                /**
                 * Upgrade Intervention Image to 3.x
                 * fit() is replaced by cover() and coverDown()
                 * @see https://image.intervention.io/v3/modifying/resizing#fitted-image-resizing
                 */
                $image->fit($realFitSize->getRoundedX(), $realFitSize->getRoundedY(), function (Constraint $constraint) {
                    $constraint->upsize();
                }, $this->parsePosition($request));
            }
        }
    }
}
