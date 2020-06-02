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
 * Based on SLIR garbage collector class
 *
 * @file GarbageCollector.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Cache;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GarbageCollector
{
    protected $cacheDirectory;
    protected $lockPath;
    protected $logger;
    protected $fs;
    protected $ttl = 604800;

    /**
     * Garbage collector.
     *
     * Clears out old files from the cache
     *
     * @param string $cacheDirectory
     * @param LoggerInterface $logger
     */
    public function __construct($cacheDirectory, LoggerInterface $logger = null)
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->lockPath = $this->cacheDirectory . '/garbageCollector.tmp';
        $this->logger = $logger;
        $this->fs = new Filesystem();
    }

    public function launch()
    {
        if (!$this->isRunning()) {
            $this->start();
            $this->deleteStaleFilesFromDirectory($this->cacheDirectory);
            $this->deleteEmptyDirectory($this->cacheDirectory);
            $this->finish();
        }
    }

    /**
     * Deletes stale files from a directory.
     *
     * Used by the garbage collector to keep the cache directories from overflowing.
     *
     * @param string $path Directory to delete stale files from
     */
    private function deleteStaleFilesFromDirectory($path)
    {
        $finder = new Finder();
        $finder->files()
               ->in($path)
               ->date('< now - ' . $this->ttl . ' seconds')
            ->notName('garbageCollector.tmp');

        foreach ($finder as $file) {
            if (!$file->isDir()) {
                $this->fs->remove($file->getPathName());
                if (null !== $this->logger) {
                    $this->logger->info('Purge file.', ['file' => $file->getPathname()]);
                }
            }
        }

        unset($finder);
    }

    /**
     * Deletes empty directory.
     *
     * Used by the garbage collector to keep the cache directories from overflowing.
     *
     * @param string $path Directory to delete empty directories from
     */
    private function deleteEmptyDirectory($path)
    {
        $finder = new Finder();
        $dirs = iterator_to_array($finder->directories()->in($path), true);

        /**
         * @var string $pathname
         * @var \SplFileInfo $dir
         */
        foreach ($dirs as $pathname => $dir) {
            if ($this->fs->exists($pathname)) {
                $fileFinder = new Finder();
                $fileFinder->files()
                           ->in($dir->getPathname())
                           ->notName('.*');

                if (iterator_count($fileFinder) === 0) {
                    $this->fs->remove($dir->getPathname());
                    if (null !== $this->logger) {
                        $this->logger->info('Delete empty folder.', ['folder' => $dir->getPathname()]);
                    }
                }

                unset($fileFinder);
            }
        }

        unset($finder);
    }

    /**
     * Checks to see if the garbage collector is currently running.
     *
     * @return boolean
     */
    private function isRunning()
    {
        if ($this->fs->exists($this->lockPath) &&
            filemtime($this->lockPath) > time() - 86400) {
            // If the file is more than 1 day old, something probably went wrong and we should run again anyway
            return true;
        } else {
            return false;
        }
    }

    /**
     * Writes a file to the cache to use as a signal that the garbage collector is currently running.
     *
     * @return void
     */
    private function start()
    {
        $msg = sprintf("GC started");
        if (null !== $this->logger) {
            $this->logger->info($msg);
        } else {
            error_log($msg);
        }

        // Create the file that tells Intervention Request that
        // the garbage collector is currently running and doesn't need to run again right now.
        $this->fs->touch($this->lockPath);
    }

    /**
     * Removes the file that signifies that the garbage collector is currently running.
     *
     * @param boolean $successful
     */
    private function finish($successful = true)
    {
        // Delete the file that tells Intervention Request that the garbage collector is running
        $this->fs->remove($this->lockPath);

        if ($successful) {
            $msg = sprintf("GC completed");
            if (null !== $this->logger) {
                $this->logger->info($msg);
            } else {
                error_log($msg);
            }
        }
    }

    /**
     * Gets the value of lockPath.
     *
     * @return mixed
     */
    public function getLockPath()
    {
        return $this->lockPath;
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
        $this->ttl = $ttl;

        return $this;
    }
}
