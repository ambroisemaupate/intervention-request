<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final class CropProcessor implements Processor
{
    public function process(Image $image, Request $request): void
    {
        $crop = $this->validateCrop($request);
        if (
            $request->query->has('crop')
            && !$request->query->has('width')
            && !$request->query->has('height')
            && 0 < count($crop)
        ) {
            $image->crop($crop[0], $crop[1]);
        }
    }

    /**
     * @return array{int, int}|array{}
     */
    public static function validateCrop(Request $request): array
    {
        $crop = [];
        $requestCrop = $request->query->get('crop');
        if (!is_string($requestCrop)) {
            return [];
        }
        preg_match('#^([0-9]+)[x\:]([0-9]+)$#', $requestCrop, $crop);

        if (isset($crop[1]) && isset($crop[2])) {
            return [(int) $crop[1], (int) $crop[2]];
        }

        return [];
    }
}
