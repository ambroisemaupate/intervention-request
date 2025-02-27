<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final class FlysystemFileResolver extends LocalFileResolver
{
    public function __construct(
        private readonly FilesystemOperator $filesystem,
        private readonly LoggerInterface $logger,
        string $tempFilePath,
    ) {
        parent::__construct($tempFilePath);
    }

    /**
     * @throws FileNotFoundException
     */
    public function resolveFile(string $relativePath): NextGenFile
    {
        /*
         * Use a next-gen file to resolve real pathname
         * if file extension contain 2 extensions
         */
        $nextgenFile = new NextGenFile($relativePath, false, $this->logger);
        /*
         * Use resource based NextGenFile to avoid storing data on disk
         */
        $nextgenFile->setFilesystem($this->filesystem);

        return $nextgenFile;
    }
}
