<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use Intervention\Image\Image;

interface FileResolverInterface
{
    public function resolveFile(mixed $relativePath): NextGenFile;

    public function assertRequestedFilePath(mixed $path): string;

    public function saveImageData(Image $data, string $path): Image;

    public function cacheFileExists(string $path): bool;

    public function deleteCacheFile(string $path): void;

    public function getSourceMimeType(string $path): string;

    public function getSourceLastModified(string $path): int;

    /**
     * @return resource
     */
    public function getCacheStream(string $path);

    public function getCacheMimeType(string $path): string;

    public function getCacheLastModified(string $path): int;
}
