<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class CropResizedProcessor extends AbstractPositionableProcessor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('crop')
            && 1 === preg_match(
                '#^([0-9]+)[x\:]([0-9]+)$#',
                (string) ($request->query->get('crop') ?? ''),
                $crop
            )
            && !$request->query->has('hotspot')
            && ($request->query->has('width') || $request->query->has('height'))
        ) {
            $fitRatio = (float) $crop[1] / (float) $crop[2];

            if ($request->query->has('width')) {
                $realFitSize = [
                    (int) $request->query->get('width'),
                    (int) round(floatval($request->query->get('width')) / $fitRatio),
                ];
            } elseif ($request->query->has('height')) {
                $realFitSize = [
                    (int) round(floatval($request->query->get('height')) * $fitRatio),
                    (int) $request->query->get('height'),
                ];
            }

            if (isset($realFitSize)) {
                /**
                 * Upgrade Intervention Image to 3.x
                 * fit() is replaced by cover() and coverDown()
                 * @see https://image.intervention.io/v3/modifying/resizing#fitted-image-resizing
                 */
                $image->fit($realFitSize[0], $realFitSize[1], function (Constraint $constraint) {
                    $constraint->upsize();
                }, $this->parsePosition($request));
            }
        }
    }
}
