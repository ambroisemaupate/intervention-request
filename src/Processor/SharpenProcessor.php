<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class SharpenProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if (
            $request->query->has('sharpen')
            && $request->query->get('sharpen') >= 0
            && $request->query->get('sharpen') <= 100
        ) {
            $image->sharpen((int) $request->query->get('sharpen'));
        }
    }
}
