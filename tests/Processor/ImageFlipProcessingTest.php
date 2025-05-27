<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\FlipProcessor;

final class ImageFlipProcessingTest extends ImageProcessingTestCase
{
    public function testFlipImageHash(): void
    {
        // test with md5 of rhino.webp with flip horizontally
        $this->processedImageHash(
            new FlipProcessor(),
            '/rhino.webp',
            '5ddf7691c1aa1be6687e9a57cf8f37b0',
            ['flip' => 'h'],
        );
        // test with md5 of rhino.webp with flip vertically
        $this->processedImageHash(
            new FlipProcessor(),
            '/rhino.webp',
            '84ea01d1b552a98025e82b652f1b5704',
            ['flip' => 'v'],
        );
    }
}
