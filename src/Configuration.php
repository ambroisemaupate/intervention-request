<?php

/**
 * Copyright © 2018, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file Configuration.php
 * @author Ambroise Maupate
 */

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
    protected string $timezone = "UTC";
    protected int $defaultQuality = 90;
    protected bool $useFileChecksum = false;
    protected ?string $pngquantPath = null;
    protected bool $lossyPng = false;
    protected ?string $pingoPath = null;
    protected bool $noAlphaPingo = false;
    protected ?string $oxipngPath = null;
    protected ?string $jpegoptimPath = null;
    protected int $responseTtl = 31536000; // 365*24*60*60

    /**
     * @return string|null
     */
    public function getPngquantPath(): ?string
    {
        return $this->pngquantPath;
    }

    /**
     * @param string|null $pngquantPath
     * @return Configuration
     */
    public function setPngquantPath(?string $pngquantPath): Configuration
    {
        $this->pngquantPath = $pngquantPath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJpegoptimPath(): ?string
    {
        return $this->jpegoptimPath;
    }

    /**
     * @param string|null $jpegoptimPath
     * @return Configuration
     */
    public function setJpegoptimPath(?string $jpegoptimPath): Configuration
    {
        $this->jpegoptimPath = $jpegoptimPath;
        return $this;
    }

    /**
     * Gets the value of caching.
     *
     * @return boolean
     */
    public function hasCaching(): bool
    {
        return $this->caching;
    }

    /**
     * Sets the value of caching.
     *
     * @param boolean $caching the caching
     * @return Configuration
     */
    public function setCaching(bool $caching): Configuration
    {
        $this->caching = (bool) $caching;

        return $this;
    }

    /**
     * Gets the value of driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Sets the value of driver.
     *
     * @param string $driver the driver
     * @return Configuration
     */
    public function setDriver(string $driver): Configuration
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Gets the value of cachePath.
     *
     * @return string
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Sets the value of cachePath.
     *
     * @param string $cachePath the cache path
     * @return Configuration
     */
    public function setCachePath(string $cachePath): Configuration
    {
        $this->cachePath = $cachePath;

        return $this;
    }

    /**
     * Gets the value of imagesPath.
     *
     * @return string
     */
    public function getImagesPath(): string
    {
        return $this->imagesPath;
    }

    /**
     * Sets the value of imagesPath.
     *
     * @param string $imagesPath the images path
     * @return Configuration
     */
    public function setImagesPath(string $imagesPath): Configuration
    {
        $this->imagesPath = $imagesPath;

        return $this;
    }

    /**
     * Gets the value of garbage collector ttl.
     *
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Sets the value of garbage collector ttl.
     *
     * @param int $ttl the ttl
     * @return Configuration
     */
    public function setTtl(int $ttl): Configuration
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Gets the value of gcProbability.
     *
     * @return int
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
     * @return Configuration
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
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Sets the value of timezone.
     *
     * @param string $timezone the timezone
     * @return Configuration
     */
    public function setTimezone(string $timezone): Configuration
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Gets the value of defaultQuality.
     *
     * @return int
     */
    public function getDefaultQuality(): int
    {
        return $this->defaultQuality;
    }

    /**
     * Sets the value of defaultQuality.
     *
     * @param integer $defaultQuality the default quality
     * @return Configuration
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
     *
     * @return bool
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
     * @return Configuration
     */
    public function setUseFileChecksum(bool $useFileChecksum): Configuration
    {
        $this->useFileChecksum = $useFileChecksum;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUsingPassThroughCache(): bool
    {
        return $this->usePassThroughCache;
    }

    /**
     * @param bool $usePassThroughCache
     * @return Configuration
     */
    public function setUsePassThroughCache(bool $usePassThroughCache): Configuration
    {
        $this->usePassThroughCache = $usePassThroughCache;
        return $this;
    }

    /**
     * @param int $responseTtl
     *
     * @return Configuration
     */
    public function setResponseTtl(int $responseTtl): Configuration
    {
        $this->responseTtl = $responseTtl;

        return $this;
    }

    public function getResponseTtl(): int
    {
        return $this->responseTtl;
    }

    /**
     * @return string|null
     */
    public function getOxipngPath(): ?string
    {
        return $this->oxipngPath;
    }

    /**
     * @param string|null $oxipngPath
     * @return Configuration
     */
    public function setOxipngPath(?string $oxipngPath): Configuration
    {
        $this->oxipngPath = $oxipngPath;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLossyPng(): bool
    {
        return $this->lossyPng;
    }

    /**
     * @param bool $lossyPng
     * @return Configuration
     */
    public function setLossyPng(bool $lossyPng): Configuration
    {
        $this->lossyPng = $lossyPng;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPingoPath(): ?string
    {
        return $this->pingoPath;
    }

    /**
     * @param string|null $pingoPath
     * @return Configuration
     */
    public function setPingoPath($pingoPath): Configuration
    {
        $this->pingoPath = $pingoPath;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNoAlphaPingo(): bool
    {
        return $this->noAlphaPingo;
    }

    /**
     * @param bool $noAlphaPingo
     * @return Configuration
     */
    public function setNoAlphaPingo(bool $noAlphaPingo): Configuration
    {
        $this->noAlphaPingo = $noAlphaPingo;
        return $this;
    }
}
