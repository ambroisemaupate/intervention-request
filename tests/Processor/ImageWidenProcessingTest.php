<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\WidenProcessor;

final class ImageWidenProcessingTest extends ImageProcessingTestCase
{
    public function testSharpenImageHash(): void
    {
        // test the md5 of testUHD.jpg with width 200
        $this->testProcessedImageHash(
            new WidenProcessor(),
            '/testUHD.jpg',
            '69b0bc28701755b168d395febc7604a3',
            ['width' => '200'],
        );
    }
}
