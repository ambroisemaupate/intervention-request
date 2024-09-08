<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class LimitColorsProcessor implements Processor
{
    /**
     * @param Image $image
     * @param Request $request
     * @return void
     */
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('background') ||
            $request->query->has('limit_color')
        ) {
            $background = $request->query->has('background') ?
                                        $request->query->get('background') :
                                        $request->query->get('limit_color');

            if (1 === preg_match('#^([0-9a-f]{6})$#', (string) ($background ?? ''))) {
                // count higher than 256 does not trigger palette creation
                $image->limitColors(257, '#' . $background);
            }
        }
    }
}
