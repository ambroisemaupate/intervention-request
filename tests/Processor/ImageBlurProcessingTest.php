<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\BlurProcessor;

final class ImageBlurProcessingTest extends ImageProcessingTestCase
{
    public function test(): void
    {
        $this->processedImageHash(
            new BlurProcessor(),
            '/rhino.webp',
            'b2dd14e94b656232831630689ff37749',
            ['blur' => '80'],
        );
    }
}
