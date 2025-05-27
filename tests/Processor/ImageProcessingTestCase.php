<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\Processor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class ImageProcessingTestCase extends TestCase
{
    public function processedImageHash(
        Processor $processor,
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
        $processor->process($image, $query);

        // save generated image in temp folder
        $image->save($outputFilePath);

        // md5 of generated image
        $actualHash = md5_file($outputFilePath);

        $this->assertEquals($expectedHash, $actualHash, 'Image hashes do not match.');
        // remove temp file
        unlink($outputFilePath);
    }

    private function getInputPath(string $imageToProcess): string
    {
        return dirname(__DIR__).'/../web/images'.$imageToProcess;
    }

    private function getOutputFilePath(string $inputPath): string
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
