<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

class Configuration
{
    protected bool $caching = true;
    protected bool $usePassThroughCache = false;
    protected string $cachePath;
    protected string $imagesPath;
    /**
     * @var string [gd or imagick]
     */
    protected string $driver = 'gd';
    protected int $ttl = 604800; // 7*24*60*60
    protected int $gcProbability = 400;
    protected string $timezone = 'UTC';
    protected int $defaultQuality = 90;
    protected bool $useFileChecksum = false;
    protected ?string $pngquantPath = null;
    protected bool $lossyPng = false;
    protected ?string $pingoPath = null;
    protected bool $noAlphaPingo = false;
    protected ?string $oxipngPath = null;
    protected ?string $jpegoptimPath = null;
    protected int $responseTtl = 31536000; // 365*24*60*60

    public function getPngquantPath(): ?string
    {
        return $this->pngquantPath;
    }

    public function setPngquantPath(?string $pngquantPath): Configuration
    {
        $this->pngquantPath = $pngquantPath;

        return $this;
    }

    public function getJpegoptimPath(): ?string
    {
        return $this->jpegoptimPath;
    }

    public function setJpegoptimPath(?string $jpegoptimPath): Configuration
    {
        $this->jpegoptimPath = $jpegoptimPath;

        return $this;
    }

    /**
     * Gets the value of caching.
     */
    public function hasCaching(): bool
    {
        return $this->caching;
    }

    /**
     * Sets the value of caching.
     *
     * @param bool $caching the caching
     */
    public function setCaching(bool $caching): Configuration
    {
        $this->caching = (bool) $caching;

        return $this;
    }

    /**
     * Gets the value of driver.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Sets the value of driver.
     *
     * @param string $driver the driver
     */
    public function setDriver(string $driver): Configuration
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Gets the value of cachePath.
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Sets the value of cachePath.
     *
     * @param string $cachePath the cache path
     */
    public function setCachePath(string $cachePath): Configuration
    {
        $this->cachePath = $cachePath;

        return $this;
    }

    /**
     * Gets the value of imagesPath.
     */
    public function getImagesPath(): string
    {
        return $this->imagesPath;
    }

    /**
     * Sets the value of imagesPath.
     *
     * @param string $imagesPath the images path
     */
    public function setImagesPath(string $imagesPath): Configuration
    {
        $this->imagesPath = $imagesPath;

        return $this;
    }

    /**
     * Gets the value of garbage collector ttl.
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Sets the value of garbage collector ttl.
     *
     * @param int $ttl the ttl
     */
    public function setTtl(int $ttl): Configuration
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Gets the value of gcProbability.
     */
    public function getGcProbability(): int
    {
        return $this->gcProbability;
    }

    /**
     * Sets the value of gcProbability.
     *
     * Garbage collection launch probability is 1/$gcProbability where
     * probability of 1/1 will launch GC at every request.
     *
     * @param int $gcProbability the gc probability
     */
    public function setGcProbability(int $gcProbability): Configuration
    {
        if ($gcProbability >= 1) {
            $this->gcProbability = $gcProbability;
        }

        return $this;
    }

    /**
     * Gets the value of timezone.
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Sets the value of timezone.
     *
     * @param string $timezone the timezone
     */
    public function setTimezone(string $timezone): Configuration
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Gets the value of defaultQuality.
     */
    public function getDefaultQuality(): int
    {
        return $this->defaultQuality;
    }

    /**
     * Sets the value of defaultQuality.
     *
     * @param int $defaultQuality the default quality
     */
    public function setDefaultQuality(int $defaultQuality): Configuration
    {
        if ($defaultQuality > 100 || $defaultQuality <= 0) {
            throw new \InvalidArgumentException('Quality must be between 1 and 100');
        }
        $this->defaultQuality = $defaultQuality;

        return $this;
    }

    /**
     * Gets the value of useFileChecksum.
     */
    public function getUseFileChecksum(): bool
    {
        return $this->useFileChecksum;
    }

    /**
     * Sets the value of useFileChecksum.
     *
     * This will enable/disable file checksum, be careful, this
     * can slow down your php process a lot if you are process large images
     * (> 1 Mo).
     *
     * @param bool $useFileChecksum the use file md5
     */
    public function setUseFileChecksum(bool $useFileChecksum): Configuration
    {
        $this->useFileChecksum = $useFileChecksum;

        return $this;
    }

    public function isUsingPassThroughCache(): bool
    {
        return $this->usePassThroughCache;
    }

    public function setUsePassThroughCache(bool $usePassThroughCache): Configuration
    {
        $this->usePassThroughCache = $usePassThroughCache;

        return $this;
    }

    public function setResponseTtl(int $responseTtl): Configuration
    {
        $this->responseTtl = $responseTtl;

        return $this;
    }

    public function getResponseTtl(): int
    {
        return $this->responseTtl;
    }

    public function getOxipngPath(): ?string
    {
        return $this->oxipngPath;
    }

    public function setOxipngPath(?string $oxipngPath): Configuration
    {
        $this->oxipngPath = $oxipngPath;

        return $this;
    }

    public function isLossyPng(): bool
    {
        return $this->lossyPng;
    }

    public function setLossyPng(bool $lossyPng): Configuration
    {
        $this->lossyPng = $lossyPng;

        return $this;
    }

    public function getPingoPath(): ?string
    {
        return $this->pingoPath;
    }

    /**
     * @param string|null $pingoPath
     */
    public function setPingoPath($pingoPath): Configuration
    {
        $this->pingoPath = $pingoPath;

        return $this;
    }

    public function isNoAlphaPingo(): bool
    {
        return $this->noAlphaPingo;
    }

    public function setNoAlphaPingo(bool $noAlphaPingo): Configuration
    {
        $this->noAlphaPingo = $noAlphaPingo;

        return $this;
    }
}
