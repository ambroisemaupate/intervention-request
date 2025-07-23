<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\FitProcessor;

final class ImageFitProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        $this->processedImageHash(
            new FitProcessor(),
            '/rhino.webp',
            'adf5dc307d7ee97ac06964b6c8210485',
            ['fit' => '500x500', 'quality' => '80'],
        );
    }
}
