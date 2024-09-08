<?php

declare(strict_types=1);

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
