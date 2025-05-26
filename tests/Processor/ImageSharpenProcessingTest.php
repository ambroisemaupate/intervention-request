<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\SharpenProcessor;

final class ImageSharpenProcessingTest extends ImageProcessingTestCase
{
    public function testSharpenImageHash(): void
    {
        $this->testProcessedImageHash(
            new SharpenProcessor(),
            '/testUHD.jpg',
            '81413b59796e3b4ed808cbf9458400ba',
            ['sharpen' => '90'],
        );
    }
}
