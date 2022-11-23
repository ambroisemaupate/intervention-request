<?php

/**
 * Copyright © 2018, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file FitProcessor.php
 * @author Ambroise Maupate
 */

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
