<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class WidenProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if (
            $request->query->has('width')
            && 1 === preg_match(
                '#^([0-9]+)$#',
                (string) ($request->query->get('width') ?? ''),
                $width
            )
        ) {
            $image->scaleDown(width: (int) $width[1]);
        }
    }
}
