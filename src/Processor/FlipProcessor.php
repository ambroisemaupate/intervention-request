<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class FlipProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if (
            $request->query->has('flip')
            && 1 === preg_match('#^(h|v)$#', (string) ($request->query->get('flip') ?? ''), $fit)
        ) {
            if ('h' === $fit[1]) {
                $image->flop();
            } else {
                $image->flip();
            }
        }
    }
}
