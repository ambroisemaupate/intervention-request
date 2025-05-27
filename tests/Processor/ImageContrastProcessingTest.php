<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\ContrastProcessor;

final class ImageContrastProcessingTest extends ImageProcessingTestCase
{
    public function testContrastImageHash(): void
    {
        $this->processedImageHash(
            new ContrastProcessor(),
            '/testUHD.jpg',
            '7b4c35faa00e48a2a710fca5d80343a4',
            ['contrast' => '100'],
        );
    }
}
