<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class ContrastProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('contrast')
            && $request->query->get('contrast') >= -100
            && $request->query->get('contrast') <= 100
        ) {
            $image->contrast((int) $request->query->get('contrast'));
        }
    }
}
