<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class SharpenProcessor implements Processor
{
    /**
     * @param Image $image
     * @param Request $request
     * @return void
     */
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('sharpen') &&
            $request->query->get('sharpen') >= 0 &&
            $request->query->get('sharpen') <= 100
        ) {
            $image->sharpen((int) $request->query->get('sharpen'));
        }
    }
}
