<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class FlipProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('flip')
            && 1 === preg_match('#^(h|v)$#', (string) ($request->query->get('flip') ?? ''), $fit)
        ) {
            $image->flip($fit[1]);
        }
    }
}
