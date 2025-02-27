<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class HeightenProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('height')
            && 1 === preg_match('#^([0-9]+)$#', (string) ($request->query->get('height') ?? ''), $height)
        ) {
            $image->heighten((int) $height[1], function (Constraint $constraint) {
                $constraint->upsize();
            });
        }
    }
}
