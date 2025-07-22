<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\HeightenProcessor;

final class ImageHeightenProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        // test with md5 of testUHD.jpg with height 200
        $this->processedImageHash(
            new HeightenProcessor(),
            '/testUHD.jpg',
            '1074a5604e9dc77180fc4be785433b8b',
            ['height' => '200'],
        );
    }
}
