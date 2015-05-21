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
    protected function setCaching($caching)
    {
        $this->caching = $caching;

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
    protected function setDriver($driver)
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
}
