<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use Intervention\Image\Image;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final readonly class FlysystemFileResolver implements FileResolverInterface
{
    use RequestedFilePathTrait;

    public function __construct(
        private FilesystemOperator $sourceFilesystem,
        private FilesystemOperator $cacheFilesystem,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws FileNotFoundException
     */
    public function resolveFile(mixed $relativePath): NextGenFile
    {
        /*
         * Use a next-gen file to resolve real pathname
         * if file extension contain 2 extensions
         */
        $nextgenFile = new NextGenFile(
            $this->assertRequestedFilePath($relativePath),
            false,
            $this->logger
        );
        /*
         * Use resource based NextGenFile to avoid storing data on disk
         */
        $nextgenFile->setFilesystem($this->sourceFilesystem);

        return $nextgenFile;
    }

    public function saveImageData(Image $data, string $path): Image
    {
        $this->cacheFilesystem->write($path, $data->getEncoded());

        if ($this->cacheFilesystem->fileExists($path)) {
            $data->mime = $this->cacheFilesystem->mimeType($path);
        }

        return $data;
    }

    public function getSourceFilesystem(): FilesystemOperator
    {
        return $this->sourceFilesystem;
    }

    public function getCacheFilesystem(): FilesystemOperator
    {
        return $this->cacheFilesystem;
    }

    public function cacheFileExists(string $path): bool
    {
        return $this->cacheFilesystem->fileExists($path);
    }

    public function deleteCacheFile(string $path): void
    {
        $this->cacheFilesystem->delete($path);
    }

    public function getCacheStream(string $path)
    {
        return $this->cacheFilesystem->readStream($path);
    }

    public function getCacheMimeType(string $path): string
    {
        return $this->cacheFilesystem->mimeType($path);
    }

    public function getCacheLastModified(string $path): int
    {
        return $this->cacheFilesystem->lastModified($path);
    }

    public function getSourceMimeType(string $path): string
    {
        return $this->sourceFilesystem->mimeType($path);
    }

    public function getSourceLastModified(string $path): int
    {
        return $this->sourceFilesystem->lastModified($path);
    }
}
