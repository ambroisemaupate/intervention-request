<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\RotateProcessor;

final class ImageRotateProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        // test with testPNG.png with rotate 20 and background transparent
        $this->processedImageHash(
            new RotateProcessor(),
            '/testPNG.png',
            'f55197d34c165efa7b1674f32c4bd326',
            [
                'rotate' => '20',
                'background' => 'transparent',
            ],
        );
    }
}
