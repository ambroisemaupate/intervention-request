<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\RotateProcessor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ImageRotateProcessingTest extends TestCase
{
    private string $inputPath = '/../web/images';
    private string $outputPath = '/temp';
    private string $miaouPath = '/testPNG.png';

    public function testRotateImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->miaouPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.png';
        // md5 of rhino.webp with rotate 20 and background transparent
        $expectedHash = 'd0d1e345cc491b8e1becdd62a4c455ca';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'rotate' => '20',
            'background' => 'transparent',
        ]);

        // process image
        $processor = new RotateProcessor();
        $processor->process($image, $query);

        // save generated image in temp folder
        if (!file_exists($tempFilePath)) {
            mkdir($outputPath, 0777, true);
        }
        $image->save($tempFilePath);

        // md5 of generated image
        $actualHash = md5_file($tempFilePath);

        $this->assertEquals($expectedHash, $actualHash, 'Image hashes do not match.');
        // remove temp file
        unlink($tempFilePath);
    }
}
