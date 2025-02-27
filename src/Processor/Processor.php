<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

interface Processor
{
    public function process(Image $image, Request $request): void;
}
