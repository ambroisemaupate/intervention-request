<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\HotspotProcessor;

final class ImageHotspotProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        // test with md5 of rhino.webp with hotspot 1x0 + crop 1x1 + width 1050
        $this->processedImageHash(
            new HotspotProcessor(),
            '/rhino.webp',
            'cf81c4032580c633b609bc3cd9662ad6',
            ['hotspot' => '1x0', 'crop' => '1x1', 'width' => '1050']
        );
    }
}
