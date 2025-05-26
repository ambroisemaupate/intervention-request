<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\FitProcessor;

final class ImageFitProcessingTest extends ImageProcessingTestCase
{
    public function testFitImageHash(): void
    {
        $this->testProcessedImageHash(
            new FitProcessor(),
            '/rhino.webp',
            '2b5b305cd8a20d52261f5a34cfabba23',
            ['fit' => '500x500', 'quality' => '80'],
        );
    }
}
