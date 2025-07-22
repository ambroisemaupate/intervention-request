<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\ContrastProcessor;

final class ImageContrastProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        $this->processedImageHash(
            new ContrastProcessor(),
            '/testUHD.jpg',
            '2d349fa300ea480e9b90ace4f8991d09',
            ['contrast' => '100'],
        );
    }
}
