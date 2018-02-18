<?php
/**
 * Copyright © 2015, Ambroise Maupate
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

/**
 *
 */
class Configuration
{
    /**
     * @var bool
     */
    protected $caching = true;
    /**
     * @var bool
     */
    protected $usePassThroughCache = false;
    /**
     * @var string
     */
    protected $cachePath;
    /**
     * @var string
     */
    protected $imagesPath;
    /**
     * @var string [gd or imagick]
     */
    protected $driver = 'gd';
    /**
     * @var int
     */
    protected $ttl = 604800; // 7*24*60*60
    /**
     * @var int
     */
    protected $gcProbability = 400;
    /**
     * @var string
     */
    protected $timezone = "UTC";
    /**
     * @var int
     */
    protected $defaultQuality = 90;
    /**
     * @var bool
     */
    protected $useFileChecksum = false;
    /**
     * @var string
     */
    protected $pngquantPath;
    /**
     * @var string
     */
    protected $jpegoptimPath;

    /**
     * @return string
     */
    public function getPngquantPath()
    {
        return $this->pngquantPath;
    }

    /**
     * @param string $pngquantPath
     * @return Configuration
     */
    public function setPngquantPath($pngquantPath)
    {
        $this->pngquantPath = $pngquantPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getJpegoptimPath()
    {
        return $this->jpegoptimPath;
    }

    /**
     * @param string $jpegoptimPath
     * @return Configuration
     */
    public function setJpegoptimPath($jpegoptimPath)
    {
        $this->jpegoptimPath = $jpegoptimPath;
        return $this;
    }

    /**
     * Gets the value of caching.
     *
     * @return boolean
     */
    public function hasCaching()
    {
        return $this->caching;
    }

    /**
     * Sets the value of caching.
     *
     * @param boolean $caching the caching
     * @return Configuration
     */
    public function setCaching($caching)
    {
        $this->caching = (boolean) $caching;

        return $this;
    }

    /**
     * Gets the value of driver.
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Sets the value of driver.
     *
     * @param string $driver the driver
     * @return Configuration
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Gets the value of cachePath.
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Sets the value of cachePath.
     *
     * @param string $cachePath the cache path
     * @return Configuration
     */
    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;

        return $this;
    }

    /**
     * Gets the value of imagesPath.
     *
     * @return mixed
     */
    public function getImagesPath()
    {
        return $this->imagesPath;
    }

    /**
     * Sets the value of imagesPath.
     *
     * @param mixed $imagesPath the images path
     * @return Configuration
     */
    public function setImagesPath($imagesPath)
    {
        $this->imagesPath = $imagesPath;

        return $this;
    }

    /**
     * Gets the value of ttl.
     *
     * @return mixed
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Sets the value of ttl.
     *
     * @param mixed $ttl the ttl
     * @return Configuration
     */
    public function setTtl($ttl)
    {
        $this->ttl = (int) $ttl;

        return $this;
    }

    /**
     * Gets the value of gcProbability.
     *
     * @return mixed
     */
    public function getGcProbability()
    {
        return $this->gcProbability;
    }

    /**
     * Sets the value of gcProbability.
     *
     * Garbage collection launch probability is 1/$gcProbability where
     * probability of 1/1 will launch GC at every request.
     *
     * @param mixed $gcProbability the gc probability
     * @return Configuration
     */
    public function setGcProbability($gcProbability)
    {
        if ($gcProbability >= 1) {
            $this->gcProbability = (int) $gcProbability;
        }

        return $this;
    }

    /**
     * Gets the value of timezone.
     *
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Sets the value of timezone.
     *
     * @param mixed $timezone the timezone
     * @return Configuration
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Gets the value of defaultQuality.
     *
     * @return mixed
     */
    public function getDefaultQuality()
    {
        return $this->defaultQuality;
    }

    /**
     * Sets the value of defaultQuality.
     *
     * @param integer $defaultQuality the default quality
     * @return Configuration
     */
    public function setDefaultQuality($defaultQuality)
    {
        $this->defaultQuality = (int) $defaultQuality;

        return $this;
    }

    /**
     * Gets the value of useFileChecksum.
     *
     * @return boolean
     */
    public function getUseFileChecksum()
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
     * @param boolean $useFileChecksum the use file md5
     * @return Configuration
     */
    public function setUseFileChecksum($useFileChecksum)
    {
        $this->useFileChecksum = (boolean) $useFileChecksum;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUsingPassThroughCache()
    {
        return $this->usePassThroughCache;
    }

    /**
     * @param bool $usePassThroughCache
     * @return Configuration
     */
    public function setUsePassThroughCache($usePassThroughCache)
    {
        $this->usePassThroughCache = $usePassThroughCache;
        return $this;
    }
}
