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
        if (
            $request->query->has('fit')
            && !$request->query->has('width')
            && !$request->query->has('height')
            && 1 === preg_match(
                '#^([0-9]+)[x\:]([0-9]+)$#',
                (string) ($request->query->get('fit') ?? ''),
                $fit
            )
        ) {
            /**
             * Upgrade Intervention Image to 3.x
             * fit() is replaced by cover() and coverDown()
             * @see https://image.intervention.io/v3/modifying/resizing#fitted-image-resizing
             */
            $image->fit((int) $fit[1], (int) $fit[2], function (Constraint $constraint) {
                $constraint->upsize();
            }, $this->parsePosition($request));
        }
    }
}
