<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @deprecated Use FlysystemFileResolver with local adapter instead
 */
final class LocalFileResolver implements FileResolverInterface
{
    use RequestedFilePathTrait;

    public function __construct(
        private readonly string $localImagesPath,
        private readonly string $cachePath,
    ) {
    }

    public function resolveFile(mixed $relativePath): NextGenFile
    {
        $nativePath = $this->localImagesPath.'/'.$this->assertRequestedFilePath($relativePath);

        return new NextGenFile($nativePath);
    }

    public function saveImageData(Image $data, string $path): Image
    {
        $realPath = $this->cachePath.'/'.$path;
        $saved = @file_put_contents($realPath, $data);

        if (false === $saved) {
            throw new NotWritableException("Can't write image data to path ({$realPath})");
        }
        // set new file info
        $data->setFileInfoFromPath($realPath);

        return $data;
    }

    public function cacheFileExists(string $path): bool
    {
        return file_exists($this->cachePath.'/'.$path);
    }

    public function deleteCacheFile(string $path): void
    {
        unlink($this->cachePath.'/'.$path);
    }

    public function getCacheStream(string $path)
    {
        $resource = fopen($this->cachePath.'/'.$path, 'rb');
        if (false === $resource) {
            throw new \RuntimeException("Can't open file ({$this->cachePath}/{$path})");
        }

        return $resource;
    }

    public function getCacheMimeType(string $path): string
    {
        return (new File($this->cachePath.'/'.$path))->getMimeType() ?? 'application/octet-stream';
    }

    public function getCacheLastModified(string $path): int
    {
        return (new File($this->cachePath.'/'.$path))->getMTime();
    }

    public function getSourceMimeType(string $path): string
    {
        return (new File($this->localImagesPath.'/'.$path))->getMimeType() ?? 'application/octet-stream';
    }

    public function getSourceLastModified(string $path): int
    {
        return (new File($this->localImagesPath.'/'.$path))->getMTime();
    }
}
