<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class BackgroundColorProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if (
            $request->query->has('background')
        ) {
            $background = $request->query->get('background');
            if (1 === preg_match('#^([0-9a-f]{6})$#', (string) ($background ?? ''))) {
                $image->blendTransparency($background);
            }
        }
    }
}
