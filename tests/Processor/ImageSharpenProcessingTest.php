<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\SharpenProcessor;

final class ImageSharpenProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        $this->processedImageHash(
            new SharpenProcessor(),
            '/testUHD.jpg',
            '83f739459ec2219ecaed9e8ca49ef4b2',
            ['sharpen' => '90'],
        );
    }
}
