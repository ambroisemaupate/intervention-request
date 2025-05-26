<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\SharpenProcessor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ImageSharpenProcessingTest extends TestCase
{
    private string $inputPath = '/../web/images';
    private string $outputPath = '/temp';
    private string $rzPath = '/testUHD.jpg';

    public function testSharpenImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rzPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.jpg';
        // md5 of testUHD.jpg with sharpening 90
        $expectedHash = '81413b59796e3b4ed808cbf9458400ba';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'sharpen' => '90',
        ]);

        // process image
        $processor = new SharpenProcessor();
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
