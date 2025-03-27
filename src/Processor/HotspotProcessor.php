<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class HotspotProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('hotspot')
            && 1 === preg_match('#^([0-9]+)[x\:]([0-9]+)[x\:]([0-9]+)[x\:]([0-9]+)$#', (string) ($request->query->get('hotspot') ?? ''), $hotspot)
        ) {
            $pointX = (int) $hotspot[1];
            $pointY = (int) $hotspot[2];

            $cropWidth = (int) $hotspot[3];
            $cropHeight = (int) $hotspot[4];

            // Calculate the top-left corner (startX, startY) to center the crop around (pointX, pointY)
            $startX = max(0, $pointX - ($cropWidth / 2));
            $startY = max(0, $pointY - ($cropHeight / 2));

            // Checks that the crop does not exceed the image dimensions
            $startX = min($startX, $image->width() - $cropWidth);
            $startY = min($startY, $image->height() - $cropHeight);

            $image->crop($cropWidth, $cropHeight, (int) $startX, (int) $startY);
        }
    }
}
