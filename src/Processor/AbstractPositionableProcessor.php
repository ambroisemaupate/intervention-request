<?php

/**
 * Copyright © 2019, Ambroise Maupate
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
 * @file AbstractProcessor.php
 * @author Ambroise Maupate
 */

namespace AM\InterventionRequest\Processor;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPositionableProcessor implements Processor
{
    /**
     * @param Request $request
     * @return string
     */
    protected function parsePosition(Request $request): string
    {
        $alignment = 'center';
        if ($request->query->has('align')) {
            $alignment = (string) ($request->query->get('align', 'c') ?? 'c');
            $availablePosition = [
                'tl' => 'top-left',
                't' => 'top',
                'tr' => 'top-right',
                'l' => 'left',
                'c' => 'center',
                'r' => 'right',
                'bl' => 'bottom-left',
                'b' => 'bottom',
                'br' => 'bottom-right',
            ];
            if (in_array($alignment, array_keys($availablePosition))) {
                return $availablePosition[$alignment];
            }
        }

        return $alignment;
    }
}
