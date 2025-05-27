<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\CropResizedProcessor;

final class ImageCropResizedProcessingTest extends ImageProcessingTestCase
{
    public function testCropResizedImageHash(): void
    {
        $this->processedImageHash(
            new CropResizedProcessor(),
            '/rhino.webp',
            'c1936cfe5dea023d7220deccb16a70c1',
            [
                'crop' => '1x1',
                'width' => '1000',
                'align' => 'center',
            ],
        );
    }
}
