<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

final class LocalFileResolver implements FileResolverInterface
{
    private string $localImagesPath;

    public function __construct(string $localImagesPath)
    {
        $this->localImagesPath = $localImagesPath;
    }

    public function resolveFile(string $relativePath): NextGenFile
    {
        $nativePath = $this->localImagesPath . '/' . $relativePath;
        return new NextGenFile($nativePath);
    }
}
