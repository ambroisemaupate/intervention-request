<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use Symfony\Component\HttpFoundation\File\File;

class NextGenFile extends File
{
    protected string $requestedPath;
    protected ?File $requestedFile;
    protected bool $isNextGen = false;
    protected ?string $nextGenMimeType = null;
    protected ?string $nextGenExtension = null;

    public function __construct(string $path, bool $checkPath = true)
    {
        if (preg_match('#\.(webp|heic|heif|avif)\.jpg$#', $path) > 0) {
            /*
             * Convert HEIC format back to JPEG
             */
            $this->isNextGen = true;
            $this->nextGenMimeType = 'image/jpeg';
            $this->nextGenExtension = 'jpg';
            $this->requestedPath = $path;
            $this->requestedFile = new File($path, false);
            $realPath = preg_replace('#\.jpg$#', '', $path);
            parent::__construct($realPath ?? '', $checkPath);
        } elseif (preg_match('#\.(jpe?g|gif|png|avif)\.heic$#', $path) > 0) {
            /*
             * HEIC format
             */
            $this->isNextGen = true;
            $this->nextGenMimeType = 'image/heic';
            $this->nextGenExtension = 'heic';
            $this->requestedPath = $path;
            $this->requestedFile = new File($path, false);
            $realPath = preg_replace('#\.heic$#', '', $path);
            parent::__construct($realPath ?? '', $checkPath);
        } elseif (preg_match('#\.(jpe?g|gif|png|heic|heif)\.avif$#', $path) > 0) {
            /*
             * AVIF format
             */
            $this->isNextGen = true;
            $this->nextGenMimeType = 'image/avif';
            $this->nextGenExtension = 'avif';
            $this->requestedPath = $path;
            $this->requestedFile = new File($path, false);
            $realPath = preg_replace('#\.avif$#', '', $path);
            parent::__construct($realPath ?? '', $checkPath);
        } elseif (preg_match('#\.(jpe?g|gif|png|avif|heic|heif)\.webp$#', $path) > 0) {
            /*
             * WebP format
             */
            $this->isNextGen = true;
            $this->nextGenMimeType = 'image/webp';
            $this->nextGenExtension = 'webp';
            $this->requestedPath = $path;
            $this->requestedFile = new File($path, false);
            $realPath = preg_replace('#\.webp$#', '', $path);
            parent::__construct($realPath ?? '', $checkPath);
        } else {
            parent::__construct($path, $checkPath);
        }
    }

    /**
     * @return string
     */
    public function getRequestedPath(): string
    {
        return $this->requestedPath;
    }

    /**
     * @return File
     */
    public function getRequestedFile(): File
    {
        return $this->requestedFile ?? $this;
    }

    /**
     * @return bool
     */
    public function isNextGen(): bool
    {
        return $this->isNextGen;
    }

    /**
     * @return string|null
     */
    public function getNextGenMimeType(): ?string
    {
        return $this->nextGenMimeType;
    }

    /**
     * @return string|null
     */
    public function getNextGenExtension(): ?string
    {
        return $this->nextGenExtension;
    }
}
