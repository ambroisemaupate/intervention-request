<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final class FitProcessor extends AbstractPositionableProcessor
{
    public function process(ImageInterface $image, Request $request): void
    {
        $fit = CropProcessor::validateDimensions($request, 'fit');
        if (
            null !== $fit
            && !$request->query->has('width')
            && !$request->query->has('height')
        ) {
            $image->coverDown($fit->getRoundedX(), $fit->getRoundedY(), $this->parsePosition($request));
        }
    }
}
