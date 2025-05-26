<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\BackgroundColorProcessor;
use AM\InterventionRequest\Processor\BlurProcessor;
use AM\InterventionRequest\Processor\ContrastProcessor;
use AM\InterventionRequest\Processor\CropProcessor;
use AM\InterventionRequest\Processor\CropResizedProcessor;
use AM\InterventionRequest\Processor\FitProcessor;
use AM\InterventionRequest\Processor\FlipProcessor;
use AM\InterventionRequest\Processor\HeightenProcessor;
use AM\InterventionRequest\Processor\HotspotProcessor;
use AM\InterventionRequest\Processor\Processor;
use AM\InterventionRequest\Processor\RotateProcessor;
use AM\InterventionRequest\Processor\SharpenProcessor;
use AM\InterventionRequest\Processor\WidenProcessor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ImageProcessingTestCase extends TestCase
{
    public function imageGeneration(
        string $processor,
        string $imageToProcess,
        string $expectedHash,
        array $queryParameters = [],
    ): void {
        $inputPath = $this->getInputPath($imageToProcess);
        // temp path of generated image
        $outputFilePath = $this->getOutputFilePath($inputPath);

        // instanciate image manager with gd driver
        $imageManager = ImageManager::gd();
        // read the image and add query parameters we want to test
        $image = $imageManager->read($inputPath);
        $query = new Request($queryParameters);

        // process image
        $processor = $this->getProcessor($processor);
        if (null === $processor) {
            return;
        }
        $processor->process($image, $query);

        // save generated image in temp folder
        $image->save($outputFilePath);

        // md5 of generated image
        $actualHash = md5_file($outputFilePath);

        $this->assertEquals($expectedHash, $actualHash, 'Image hashes do not match.');
        // remove temp file
        unlink($outputFilePath);
    }

    protected function getProcessor(string $processor): ?Processor
    {
        return match ($processor) {
            'background' => new BackgroundColorProcessor(),
            'blur' => new BlurProcessor(),
            'contrast' => new ContrastProcessor(),
            'crop' => new CropProcessor(),
            'cropResized' => new CropResizedProcessor(),
            'fit' => new FitProcessor(),
            'flip' => new FlipProcessor(),
            'heighten' => new HeightenProcessor(),
            'hotspot' => new HotspotProcessor(),
            'rotate' => new RotateProcessor(),
            'sharpen' => new SharpenProcessor(),
            'widen' => new WidenProcessor(),
            default => null,
        };
    }

    protected function getInputPath(string $imageToProcess): string
    {
        return dirname(__DIR__).'/../web/images'.$imageToProcess;
    }

    protected function getOutputFilePath(string $inputPath): string
    {
        $extension = pathinfo($inputPath, PATHINFO_EXTENSION);
        $outputPath = dirname(__DIR__).'/temp';
        $outputFilePath = $outputPath.'/temp.'.$extension;

        if (!file_exists($outputFilePath)) {
            mkdir($outputPath, 0777, true);
        }

        return $outputFilePath;
    }
}
