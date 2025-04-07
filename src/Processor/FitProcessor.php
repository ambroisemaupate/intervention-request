<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class FitProcessor extends AbstractPositionableProcessor
{
    public function process(Image $image, Request $request): void
    {
        $fit = CropProcessor::validateDimensions($request, 'fit');
        if (
            null !== $fit
            && !$request->query->has('width')
            && !$request->query->has('height')
        ) {
            /*
             * Upgrade Intervention Image to 3.x
             * fit() is replaced by cover() and coverDown()
             * @see https://image.intervention.io/v3/modifying/resizing#fitted-image-resizing
             */
            $image->fit($fit->getRoundedX(), $fit->getRoundedY(), function (Constraint $constraint) {
                $constraint->upsize();
            }, $this->parsePosition($request));
        }
    }
}
