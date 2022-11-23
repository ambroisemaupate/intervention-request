<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

interface FileResolverInterface
{
    public function resolveFile(string $relativePath): NextGenFile;

    /**
     * @param mixed $path
     * @return string
     * @throws \InvalidArgumentException
     */
    public function assertRequestedFilePath($path): string;
}
