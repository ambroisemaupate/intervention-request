<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use League\Flysystem\FilesystemOperator;

interface FileWithResourceInterface
{
    /**
     * @return $this
     */
    public function setFilesystem(FilesystemOperator $filesystem): FileWithResourceInterface;

    /**
     * @return resource|null
     */
    public function getResource();
}
