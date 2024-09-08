<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Intervention\Image\Constraint;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package AM\InterventionRequest\Processor
 */
final class FitProcessor extends AbstractPositionableProcessor
{
    /**
     * @param Image $image
     * @param Request $request
     * @return void
     */
    public function process(Image $image, Request $request): void
    {
        if (
            $request->query->has('fit') &&
            !$request->query->has('width') &&
            !$request->query->has('height') &&
            1 === preg_match(
                '#^([0-9]+)[x\:]([0-9]+)$#',
                (string) ($request->query->get('fit') ?? ''),
                $fit
            )
        ) {
            $image->fit((int) $fit[1], (int) $fit[2], function (Constraint $constraint) {
                $constraint->upsize();
            }, $this->parsePosition($request));
        }
    }
}
