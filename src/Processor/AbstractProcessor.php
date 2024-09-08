<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

/**
 * Define an abstract image processor class.
 *
 * Extend this class if you want to create your own
 * image processor. All your process should be contained in
 * `process` method.
 * @deprecated  Use Processor interface
 */
abstract class AbstractProcessor implements Processor
{
    /**
     * Execute image intervention operations.
     *
     * @param Image $image
     * @param Request $request
     */
    abstract public function process(Image $image, Request $request): void;
}
