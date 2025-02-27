<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

interface FileResolverInterface
{
    public function resolveFile(string $relativePath): NextGenFile;

    /**
     * @throws \InvalidArgumentException
     */
    public function assertRequestedFilePath(mixed $path): string;
}
