<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class BlurProcessor implements Processor
{
    /**
     * @param Image $image
     * @param Request $request
     */
    public function process(Image $image, Request $request): void
    {
        if ($request->query->has('blur')) {
            $image->blur((int) $request->query->get('blur'));
        }
    }
}
