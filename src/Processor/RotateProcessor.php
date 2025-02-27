<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class RotateProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if ($request->query->has('rotate')) {
            $image->rotate((float) $request->query->get('rotate'));
        }
    }
}
