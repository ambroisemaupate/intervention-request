<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProgressiveProcessor implements Processor
{
    public function process(ImageInterface $image, Request $request): void
    {
        if (
            $request->query->has('progressive')
            || $request->query->has('interlace')
        ) {
            $process = $request->query->has('progressive') ?
                                        $request->query->get('progressive') :
                                        $request->query->get('interlace');

            $image->encodeByMediaType(progressive: (bool) $process);
        }
    }
}
