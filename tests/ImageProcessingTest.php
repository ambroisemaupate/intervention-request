<?php

declare(strict_types=1);

use AM\InterventionRequest\Processor\BackgroundColorProcessor;
use AM\InterventionRequest\Processor\BlurProcessor;
use AM\InterventionRequest\Processor\ContrastProcessor;
use AM\InterventionRequest\Processor\CropProcessor;
use AM\InterventionRequest\Processor\FitProcessor;
use AM\InterventionRequest\Processor\FlipProcessor;
use AM\InterventionRequest\Processor\HeightenProcessor;
use AM\InterventionRequest\Processor\HotspotProcessor;
use AM\InterventionRequest\Processor\RotateProcessor;
use AM\InterventionRequest\Processor\SharpenProcessor;
use AM\InterventionRequest\Processor\WidenProcessor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ImageProcessingTest extends TestCase
{
    private string $inputPath = '/web/images';
    private string $outputPath = '/tests/temp';
    private string $rhinoPath = '/rhino.webp';
    private string $miaouPath = '/testPNG.png';
    private string $rzPath = '/testUHD.jpg';

    public function testFitImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rhinoPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with fit 500 x 500
        $expectedHash = '2b5b305cd8a20d52261f5a34cfabba23';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'fit' => '500x500',
            'quality' => '80',
        ]);

        // process image
        $processor = new FitProcessor();
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

    public function testCropImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rhinoPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with crop 1000 x 1000
        $expectedHash = '58fdd35900ba9550ce73341883a7fc26';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'crop' => '1000x1000',
        ]);

        // process image
        $processor = new CropProcessor();
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

    public function testBackgroundImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->miaouPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of testPNG.png with background #ff0000
        $expectedHash = '82a51d800c78ec2de1992830c51683f4';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'background' => 'ff0000',
        ]);

        // process image
        $processor = new BackgroundColorProcessor();
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

    public function testContrastImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rzPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of testUHD.jpg with contrast 100
        $expectedHash = 'b3149116e7bac491eb3c2b7bf8b24327';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'contrast' => '100',
        ]);

        // process image
        $processor = new ContrastProcessor();
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

    public function testHotspotImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rhinoPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with hotspot 1x0 + crop 1x1 + width 1050
        $expectedHash = 'cf81c4032580c633b609bc3cd9662ad6';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'hotspot' => '1x0',
            'crop' => '1x1',
            'width' => '1050',
        ]);

        // process image
        $processor = new HotspotProcessor();
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

    public function testRotateImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->miaouPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with rotate 20 and background transparent
        $expectedHash = '3b7bb70fdaf9625016b498f2f0890067';

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

    public function testSharpenImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rzPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of testUHD.jpg with sharpening 90
        $expectedHash = 'db3b4a57e4db34bf2a45cdf78710bbb7';

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

    public function testWidenImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rzPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with width 200
        $expectedHash = '8323c185ad7181be3b922d0964e0a716';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'width' => '200',
        ]);

        // process image
        $processor = new WidenProcessor();
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

    public function testHeightenImageGeneration(): void
    {
        // path of image to be processed
        $inputPath = dirname(__DIR__).$this->inputPath.$this->rzPath;
        // temp path of generated image
        $outputPath = dirname(__DIR__).$this->outputPath;
        $tempFilePath = $outputPath.'/temp.webp';
        // md5 of rhino.webp with height 200
        $expectedHash = '6b00a8b0bd552713ea8390ab31c2a74c';

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request([
            'height' => '200',
        ]);

        // process image
        $processor = new HeightenProcessor();
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
