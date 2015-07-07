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
    protected $caching = true;
    protected $cachePath;
    protected $imagesPath;
    protected $driver = 'gd';
    protected $ttl = 604800; // 7*24*60*60
    protected $gcProbability = 400;
    protected $timezone = "UTC";
    protected $defaultQuality = 90;
    protected $useFileMd5 = false;

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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
     */
    public function setDefaultQuality($defaultQuality)
    {
        $this->defaultQuality = (int) $defaultQuality;

        return $this;
    }

    /**
     * Gets the value of useFileMd5.
     *
     * @return boolean
     */
    public function getUseFileMd5()
    {
        return $this->useFileMd5;
    }

    /**
     * Sets the value of useFileMd5.
     *
     * This will enable/disable md5 file checking, be careful, this
     * can slow down your php process a lot if you are process large images
     * (> 1 Mo).
     *
     * @param boolean $useFileMd5 the use file md5
     *
     * @return self
     */
    public function setUseFileMd5($useFileMd5)
    {
        $this->useFileMd5 = (boolean) $useFileMd5;

        return $this;
    }
}
