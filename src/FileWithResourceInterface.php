<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use League\Flysystem\FilesystemOperator;

interface FileWithResourceInterface
{
    /**
     * @param FilesystemOperator $filesystem
     * @return $this
     */
    public function setFilesystem(FilesystemOperator $filesystem): FileWithResourceInterface;
    /**
     * @return resource|null
     */
    public function getResource();
}
