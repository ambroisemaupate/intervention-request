<?php
declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

interface Processor
{
    /**
     * @param Image   $image
     * @param Request $request
     * @return void
     */
    public function process(Image $image, Request $request);
}
