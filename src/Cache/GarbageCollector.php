<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Cache;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class GarbageCollector
{
    private string $lockPath;
    private Filesystem $fs;

    /**
     * Clears out old files from the cache.
     */
    public function __construct(
        private readonly string $cacheDirectory,
        private readonly LoggerInterface $logger,
        private readonly int $ttl = 604800,
    ) {
        $this->lockPath = $this->cacheDirectory.'/garbageCollector.tmp';
        $this->fs = new Filesystem();
    }

    public function launch(): void
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
    private function deleteStaleFilesFromDirectory(string $path): void
    {
        $finder = new Finder();
        $finder->files()
               ->in($path)
               ->date('< now - '.$this->ttl.' seconds')
            ->notName('garbageCollector.tmp');

        foreach ($finder as $file) {
            if (!$file->isDir()) {
                $this->fs->remove($file->getPathName());
                $this->logger->debug('Purge file.', ['file' => $file->getPathname()]);
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
    private function deleteEmptyDirectory(string $path): void
    {
        $finder = new Finder();
        $dirs = iterator_to_array($finder->directories()->in($path), true);

        /**
         * @var string       $pathname
         * @var \SplFileInfo $dir
         */
        foreach ($dirs as $pathname => $dir) {
            if ($this->fs->exists($pathname)) {
                $fileFinder = new Finder();
                $fileFinder->files()
                           ->in($dir->getPathname())
                           ->notName('.*');

                if (0 === iterator_count($fileFinder)) {
                    $this->fs->remove($dir->getPathname());
                    $this->logger->debug('Delete empty folder.', ['folder' => $dir->getPathname()]);
                }

                unset($fileFinder);
            }
        }

        unset($finder);
    }

    /**
     * Checks to see if the garbage collector is currently running.
     */
    private function isRunning(): bool
    {
        if (
            $this->fs->exists($this->lockPath)
            && filemtime($this->lockPath) > time() - 86400
        ) {
            // If the file is more than 1 day old, something probably went wrong and we should run again anyway
            return true;
        } else {
            return false;
        }
    }

    /**
     * Writes a file to the cache to use as a signal that the garbage collector is currently running.
     */
    private function start(): void
    {
        $this->logger->debug(sprintf('GC started'));

        // Create the file that tells Intervention Request that
        // the garbage collector is currently running and doesn't need to run again right now.
        $this->fs->touch($this->lockPath);
    }

    /**
     * Removes the file that signifies that the garbage collector is currently running.
     */
    private function finish(bool $successful = true): void
    {
        // Delete the file that tells Intervention Request that the garbage collector is running
        $this->fs->remove($this->lockPath);

        if ($successful) {
            $this->logger->debug(sprintf('GC completed'));
        }
    }

    /**
     * Gets the value of lockPath.
     */
    public function getLockPath(): string
    {
        return $this->lockPath;
    }

    /**
     * Gets the value of ttl.
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}
