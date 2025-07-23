<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\WidenProcessor;

final class ImageWidenProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        // test the md5 of testUHD.jpg with width 200
        $this->processedImageHash(
            new WidenProcessor(),
            '/testUHD.jpg',
            'f8b5da72d41c4b3ec80a8b16cfd38949',
            ['width' => '200'],
        );
    }
}
