<?php

declare(strict_types=1);

use AM\InterventionRequest\Processor\FitProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Intervention\Image\ImageManager;

class ImageProcessingTest extends TestCase
{
    public function testFitImageGeneration(): void
    {
        // md5 of rhino.webp with fit 500 x 500
        $expectedHash = 'a543141705444fe939f60aa68f013dd7';

        $imageManager = ImageManager::gd();
        $image = $imageManager->read('var/www/html/web/images/rhino.webp');

        $query = new Request([
            'width' => 500,
            'height' => 500,
        ]);

        $processor = new FitProcessor();
        $processor->process($image, $query);
        $image->save('var/www/html/tests/temp/rhino-fit.webp');
        $actualHash = md5_file('var/www/html/tests/temp/rhino-fit.webp');

        $this->assertEquals($expectedHash, $actualHash, 'L’image générée ne correspond pas à l’image attendue.');
    }
}
