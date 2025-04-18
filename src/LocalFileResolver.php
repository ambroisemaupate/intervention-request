<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

class LocalFileResolver implements FileResolverInterface
{
    public function __construct(private readonly string $localImagesPath)
    {
    }

    public function resolveFile(string $relativePath): NextGenFile
    {
        $nativePath = $this->localImagesPath.'/'.$relativePath;

        return new NextGenFile($nativePath);
    }

    public function assertRequestedFilePath(mixed $path): string
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Image path must be set');
        }
        $path = trim($path);
        if ('' === $path || '/' === $path) {
            throw new \InvalidArgumentException('Image path cannot be empty');
        }
        if (str_contains($path, '../')) {
            throw new \InvalidArgumentException('Image path cannot contain parent directories');
        }
        if (str_ends_with($path, '/')) {
            throw new \InvalidArgumentException('Image path cannot be a directory');
        }

        return $path;
    }
}
