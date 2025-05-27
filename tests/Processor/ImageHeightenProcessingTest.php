<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\HeightenProcessor;

final class ImageHeightenProcessingTest extends ImageProcessingTestCase
{
    public function testHightenImageHash(): void
    {
        // test with md5 of testUHD.jpg with height 200
        $this->processedImageHash(
            new HeightenProcessor(),
            '/testUHD.jpg',
            'd98795afa87279d738a86c51fd9c0c0a',
            ['height' => '200'],
        );
    }
}
