<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

class ImageFitTestProcessingTest extends ImageProcessingTestCase
{
    public function testFit(): void
    {
        $this->imageGeneration(
            'fit',
            '/rhino.webp',
            '2b5b305cd8a20d52261f5a34cfabba23',
            ['fit' => '500x500', 'quality' => '80'],
        );
    }
}
