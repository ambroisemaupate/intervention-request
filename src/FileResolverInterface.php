<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

interface FileResolverInterface
{
    public function resolveFile(string $relativePath): NextGenFile;
}
