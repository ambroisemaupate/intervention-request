<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Intervention\Image\Constraint;
use Symfony\Component\HttpFoundation\Request;

final class WidenProcessor implements Processor
{
    /**
     * @param Image $image
     * @param Request $request
     * @return void
     */
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('width') &&
            1 === preg_match(
                '#^([0-9]+)$#',
                (string) ($request->query->get('width') ?? ''),
                $width
            )
        ) {
            $image->widen((int) $width[1], function (Constraint $constraint) {
                $constraint->upsize();
            });
        }
    }
}
