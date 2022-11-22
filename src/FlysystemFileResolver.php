<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final class FlysystemFileResolver extends LocalFileResolver
{
    private Filesystem $filesystem;
    private Filesystem $localFilesystem;
    private string $tempFilePath;

    public function __construct(Filesystem $filesystem, string $tempFilePath)
    {
        parent::__construct($tempFilePath);
        $this->filesystem = $filesystem;
        $this->tempFilePath = $tempFilePath;

        // The internal adapter
        $adapter = new LocalFilesystemAdapter(
            $this->tempFilePath,
        );
        $this->localFilesystem = new Filesystem($adapter);
    }

    public function resolveFile(string $relativePath): NextGenFile
    {
//        try {
            if (!$this->localFilesystem->fileExists($relativePath)) {
                $this->localFilesystem->writeStream($relativePath, $this->filesystem->readStream($relativePath));
            }
//        } catch (FilesystemException | UnableToReadFile $exception) {
//            // handle the error
//            throw new FileNotFoundException($relativePath);
//        }

        return parent::resolveFile($relativePath);
    }
}
