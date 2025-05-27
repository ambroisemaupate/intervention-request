<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class RotateProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if ($request->query->has('rotate')) {
            $image->rotate((float) $request->query->get('rotate'), (string) ($request->query->get('background') ?? 'ffffff'));
        }
    }
}
