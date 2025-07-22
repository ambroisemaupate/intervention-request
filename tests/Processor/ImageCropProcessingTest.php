<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\CropProcessor;

final class ImageCropProcessingTest extends ImageProcessingTestCase
{
    public function testCropImageHash(): void
    {
        $this->processedImageHash(
            new CropProcessor(),
            '/rhino.webp',
            '7f43d5f2c6553b2c83c2d690c978c7d7',
            ['crop' => '1000x1000'],
        );
    }
}
