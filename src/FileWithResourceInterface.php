<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use League\Flysystem\Filesystem;

interface FileWithResourceInterface
{
    /**
     * @param Filesystem $filesystem
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem): FileWithResourceInterface;
    /**
     * @return resource|null
     */
    public function getResource();
}
