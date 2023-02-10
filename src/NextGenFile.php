<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class NextGenFile extends File implements FileWithResourceInterface
{
    protected string $requestedPath;
    protected ?File $requestedFile;
    protected bool $isNextGen = false;
    protected ?string $nextGenMimeType = null;
    protected ?string $nextGenExtension = null;
    /**
     * @var resource|null
     */
    protected $resource = null;
    protected ?FilesystemOperator $filesystem = null;
    private LoggerInterface $logger;

    public function __construct(string $path, bool $checkPath = true, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
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
     * @return resource|null
     * @throws FileNotFoundException
     */
    public function getResource()
    {
        if (null === $this->resource) {
            if (null === $this->filesystem) {
                return null;
            }
            try {
                $this->logger->info('Read stream from ' . $this->getPathname());
                $this->resource = $this->filesystem->readStream($this->getPathname());
            } catch (FilesystemException $exception) {
                $this->logger->error($exception);
                throw new FileNotFoundException($this->getPathname());
            }
        }
        return $this->resource;
    }

    public function setFilesystem(FilesystemOperator $filesystem): FileWithResourceInterface
    {
        $this->filesystem = $filesystem;
        return $this;
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
