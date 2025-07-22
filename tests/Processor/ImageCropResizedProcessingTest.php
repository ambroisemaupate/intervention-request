<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\CropResizedProcessor;

final class ImageCropResizedProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        $this->processedImageHash(
            new CropResizedProcessor(),
            '/rhino.webp',
            'b1df5c456d33069f8e8fea650f995bbe',
            [
                'crop' => '1x1',
                'width' => '1000',
                'align' => 'center',
            ],
        );
    }
}
