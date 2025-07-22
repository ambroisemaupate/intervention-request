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
            'f96b9143c1571fbcde3abfb0a72e5ef3',
            [
                'rotate' => '20',
                'background' => 'transparent',
            ],
        );
    }
}
