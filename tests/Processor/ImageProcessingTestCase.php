<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Processor;

use AM\InterventionRequest\Processor\Processor;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
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

        // instantiate image manager with gd driver
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
    }

    private function getInputPath(string $imageToProcess): string
    {
        return dirname(__DIR__).'/../public/images'.$imageToProcess;
    }

    protected function setUp(): void
    {
        $cachePath = dirname(__DIR__).'/temp';
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($cachePath)) {
            $fileSystem->mkdir($cachePath);
        }
    }

    protected function tearDown(): void
    {
        $cachePath = dirname(__DIR__).'/temp';
        $fileSystem = new Filesystem();
        $pattern = glob($cachePath.'/*');
        if (false === $pattern) {
            return;
        }
        foreach ($pattern as $file) {
            $fileSystem->remove($file);
        }
    }

    private function getOutputFilePath(string $inputPath): string
    {
        $extension = pathinfo($inputPath, PATHINFO_EXTENSION);
        $outputPath = dirname(__DIR__).'/temp';

        return $outputPath.'/temp.'.$extension;
    }
}
