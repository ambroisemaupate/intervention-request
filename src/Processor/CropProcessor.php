<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class CropProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('crop')
            && !$request->query->has('width')
            && !$request->query->has('height')
            && 1 === preg_match('#^([0-9]+)[x\:]([0-9]+)$#', (string) ($request->query->get('crop') ?? ''), $crop)
        ) {
            $image->crop((int) $crop[1], (int) $crop[2]);
        }
    }
}
