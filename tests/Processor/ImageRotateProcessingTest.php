<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\RotateProcessor;

final class ImageRotateProcessingTest extends ImageProcessingTestCase
{
    public function testHotspotImageHash(): void
    {
        // test with testPNG.png with rotate 20 and background transparent
        $this->processedImageHash(
            new RotateProcessor(),
            '/testPNG.png',
            'd0d1e345cc491b8e1becdd62a4c455ca',
            [
                'rotate' => '20',
                'background' => 'transparent',
            ],
        );
    }
}
