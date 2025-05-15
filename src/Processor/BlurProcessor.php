<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class BlurProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if ($request->query->has('blur')) {
            $image->blur((int) $request->query->get('blur'));
        }
    }
}
