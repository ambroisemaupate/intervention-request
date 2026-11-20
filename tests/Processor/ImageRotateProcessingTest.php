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
            // PHP 8.5 bundled libgd rotates differently
            \PHP_VERSION_ID >= 80500 ? 'f55197d34c165efa7b1674f32c4bd326' : 'f96b9143c1571fbcde3abfb0a72e5ef3',
            [
                'rotate' => '20',
                'background' => 'transparent',
            ],
        );
    }
}
