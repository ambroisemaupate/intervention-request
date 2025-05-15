<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class HeightenProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if (
            $request->query->has('height')
            && 1 === preg_match('#^([0-9]+)$#', (string) ($request->query->get('height') ?? ''), $height)
        ) {
            $image->scaleDown(height: (int) $height[1]);
        }
    }
}
