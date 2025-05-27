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
            '58fdd35900ba9550ce73341883a7fc26',
            ['crop' => '1000x1000'],
        );
    }
}
