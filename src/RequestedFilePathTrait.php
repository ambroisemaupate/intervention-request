<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

trait RequestedFilePathTrait
{
    /**
     * @throws \InvalidArgumentException When requested image path is not valid
     */
    public function assertRequestedFilePath(mixed $path): string
    {
        if (!\is_string($path)) {
            throw new \InvalidArgumentException('Image path must be set');
        }
        $path = \trim($path);
        if ('' === $path || '/' === $path) {
            throw new \InvalidArgumentException('Image path cannot be empty');
        }
        if (\str_contains($path, '../')) {
            throw new \InvalidArgumentException('Image path cannot contain parent directories');
        }
        if (\str_ends_with($path, '/')) {
            throw new \InvalidArgumentException('Image path cannot be a directory');
        }

        return $path;
    }
}
