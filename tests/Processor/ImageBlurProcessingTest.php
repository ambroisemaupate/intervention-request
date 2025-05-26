<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\BlurProcessor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ImageBlurProcessingTest extends TestCase
{
    private string $inputPath = '/../web/images';
    private string $outputPath = '/temp';
    private string $rhinoPath = '/rhino.webp';

    public function testBlurImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rhinoPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with blur 80
        $expectedHash = 'b2dd14e94b656232831630689ff37749';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'blur' => '80',
        ]);

        // process image
        $processor = new BlurProcessor();
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
