<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class ProgressiveProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('progressive')
            || $request->query->has('interlace')
        ) {
            $process = $request->query->has('progressive') ?
                                        $request->query->get('progressive') :
                                        $request->query->get('interlace');

            /**
             * Upgrade Intervention Image to 3.x
             * interlace() no longer exists and is handle by encoder options.
             * @see https://image.intervention.io/v3/basics/image-output
             */
            $image->interlace((bool) $process);
        }
    }
}
