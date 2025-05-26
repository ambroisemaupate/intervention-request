<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\FlipProcessor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ImageFlipProcessingTest extends TestCase
{
    private string $inputPath = '/../web/images';
    private string $outputPath = '/temp';
    private string $rhinoPath = '/rhino.webp';

    public function testFlip(): void
    {
        // test with md5 of rhino.webp with flip horizontally
        $this->flipImageGeneration('h', '5ddf7691c1aa1be6687e9a57cf8f37b0');
        // test with md5 of rhino.webp with flip vertically
        $this->flipImageGeneration('v', '84ea01d1b552a98025e82b652f1b5704');
    }

    public function flipImageGeneration(string $flip, string $expectedHash): void
    {
        if (!in_array($flip, ['h', 'v'])) {
            $this->markTestSkipped('Flip parameter must be either h or v.');
        }
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rhinoPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'flip' => $flip,
        ]);

        // process image
        $processor = new FlipProcessor();
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
