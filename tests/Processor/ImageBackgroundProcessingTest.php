<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\BackgroundColorProcessor;

final class ImageBackgroundProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        // test with md5 of testPNG.png.jpg with background red
        $this->processedImageHash(
            new BackgroundColorProcessor(),
            '/testPNG.png',
            'b53e49a0db123ebbaf5540c5fa1d1997',
            ['background' => 'ff0000']
        );
    }
}
