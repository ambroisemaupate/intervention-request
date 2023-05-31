<?php

/**
 * Copyright © 2020, Ambroise Maupate
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
 * @file FileCache.php
 * @author Ambroise Maupate
 */

namespace AM\InterventionRequest\Cache;

use AM\InterventionRequest\Encoder\ImageEncoder;
use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\FileResolverInterface;
use AM\InterventionRequest\FileWithResourceInterface;
use AM\InterventionRequest\NextGenFile;
use AM\InterventionRequest\Processor\ChainProcessor;
use AM\InterventionRequest\ShortUrlExpander;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileCache implements EventSubscriberInterface
{
    protected string $cachePath;
    protected ?LoggerInterface $logger = null;
    protected int $ttl;
    protected int $gcProbability;
    protected bool $useFileChecksum;
    protected ChainProcessor $chainProcessor;
    private ImageEncoder $imageEncoder;
    private FileResolverInterface $fileResolver;

    /**
     * @param ChainProcessor $chainProcessor
     * @param FileResolverInterface $fileResolver
     * @param string $cachePath
     * @param LoggerInterface|null $logger
     * @param int $ttl
     * @param int $gcProbability
     * @param bool $useFileChecksum
     */
    public function __construct(
        ChainProcessor $chainProcessor,
        FileResolverInterface $fileResolver,
        string $cachePath,
        LoggerInterface $logger = null,
        int $ttl = 604800,
        int $gcProbability = 300,
        bool $useFileChecksum = false
    ) {
        $cachePath = realpath($cachePath);
        if (false === $cachePath) {
            throw new \InvalidArgumentException($cachePath . ' path does not exist.');
        }
        $this->cachePath = $cachePath;
        $this->logger = $logger;
        $this->ttl = $ttl;
        $this->gcProbability = $gcProbability;
        $this->imageEncoder = new ImageEncoder();
        $this->useFileChecksum = $useFileChecksum;
        $this->chainProcessor = $chainProcessor;
        $this->fileResolver = $fileResolver;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', -100]
        ];
    }

    /**
     * @param Image  $image
     * @param string $cacheFilePath
     * @param int    $quality
     *
     * @return Image
     */
    protected function saveImage(Image $image, string $cacheFilePath, int $quality): Image
    {
        $path = dirname($cacheFilePath);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $this->imageEncoder->save($image, $cacheFilePath, $quality);
    }

    /**
     * Determines if the garbage collector should run for this request.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function garbageCollectionShouldRun(Request $request): bool
    {
        if (true === (bool) $request->get('force_gc', false)) {
            return true;
        }

        if (mt_rand(1, $this->gcProbability) <= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks to see if the garbage collector should be initialized, and if it should, initializes it.
     *
     * @param Request $request
     * @return void
     */
    protected function initializeGarbageCollection(Request $request): void
    {
        if ($this->garbageCollectionShouldRun($request)) {
            $garbageCollector = new GarbageCollector($this->cachePath, $this->logger);
            $garbageCollector->setTtl($this->ttl);
            $garbageCollector->launch();
        }
    }

    /**
     * @param RequestEvent $requestEvent
     * @return bool
     */
    protected function supports(RequestEvent $requestEvent): bool
    {
        $config = $requestEvent->getInterventionRequest()->getConfiguration();
        return $config->hasCaching() && !$config->isUsingPassThroughCache();
    }

    protected function copyToCache(File $nativeFile, File $cachedFile): void
    {
        $fileSystem = new Filesystem();
        if (
            $nativeFile instanceof FileWithResourceInterface &&
            $nativeFile->getResource() !== null
        ) {
            $fileSystem->dumpFile($cachedFile, $nativeFile->getResource());
        } else {
            $fileSystem->copy($nativeFile, $cachedFile);
        }
    }

    /**
     * @param RequestEvent             $requestEvent
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     * @throws \Exception
     * @return void
     */
    public function onRequest(RequestEvent $requestEvent, string $eventName, EventDispatcherInterface $dispatcher): void
    {
        if ($this->supports($requestEvent)) {
            $request = $requestEvent->getRequest();
            $nativeImage = $this->fileResolver->resolveFile(
                $this->fileResolver->assertRequestedFilePath($request->get('image'))
            );
            $cacheFilePath = $this->getCacheFilePath($request, $nativeImage);
            $cacheFile = new File($cacheFilePath, false);
            $firstGen = false;

            /**
             * Also check date, if cached date is lower then original date -> Remove cached file
             */
            $mtime_original_file = $nativeImage->getRequestedFile()->getMTime();
            $mtime_cached_file = $cacheFile->getMTime();
            if (
                ($mtime_original_file !== false) && 
                ($mtime_cached_file !== false) &&
                ($mtime_cached_file < $mtime_original_file)
            ) {
                unlink($cacheFilePath);
            }
            
            /*
             * First render cached image file.
             */
            if (!is_file($cacheFilePath)) {
                if ($request->query->has('no_process')) {
                    $image = null;
                    $this->copyToCache($nativeImage, $cacheFile);
                } else {
                    $image = $this->chainProcessor->process($nativeImage, $request);
                    $this->saveImage($image, $cacheFilePath, $requestEvent->getQuality());
                }
                // create the ImageSavedEvent and dispatch it
                $dispatcher->dispatch(new ImageSavedEvent($image, $cacheFile, $requestEvent->getQuality()));
                $firstGen = true;
            }

            $fileContent = file_get_contents($cacheFile->getPathname());
            if (false !== $fileContent) {
                $response = new Response(
                    $fileContent,
                    Response::HTTP_OK,
                    [
                        'Content-Type' => $cacheFile->getMimeType(),
                        'Content-Disposition' => 'filename="' . $nativeImage->getRequestedFile()->getFilename() . '"',
                        'X-IR-Cached' => '1',
                        'X-IR-First-Gen' => (int) $firstGen
                    ]
                );
                $response->setLastModified(new \DateTime(date("Y-m-d H:i:s", $cacheFile->getMTime())));
            } else {
                $response = new Response(
                    null,
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->initializeGarbageCollection($request);
            $requestEvent->setResponse($response);
        }
    }

    /**
     * @param Request $request
     * @param File    $nativeImage
     *
     * @return string
     */
    protected function getCacheFilePath(Request $request, File $nativeImage): string
    {
        /*
         * Get file MD5 to check real image integrity
         */
        if ($this->useFileChecksum === true) {
            $fileMd5 = hash_file('adler32', $nativeImage->getPathname());
        } else {
            $fileMd5 = $nativeImage->getPathname();
        }

        /*
         * Generate a unique cache hash key
         * which will be used as image path
         *
         * The key vary on request ALLOWED params and file md5
         * if enabled.
         */
        $cacheParams = [];
        foreach ($request->query->all() as $name => $value) {
            if (in_array($name, ShortUrlExpander::getAllowedOperationsNames())) {
                $cacheParams[$name] = $value;
            }
        }
        if ($nativeImage instanceof NextGenFile && $nativeImage->isNextGen()) {
            $cacheParams[$nativeImage->getNextGenExtension()] = true;
            $extension = $nativeImage->getNextGenExtension();
        } else {
            $cacheParams['webp'] = false;
            $cacheParams['avif'] = false;
            if (false === $nativeImage->getRealPath()) {
                throw new \InvalidArgumentException('Native image does not exist.');
            }
            $extension = $this->imageEncoder->getImageAllowedExtension($nativeImage->getRealPath());
        }
        $cacheHash = hash('sha1', serialize($cacheParams) . $fileMd5);

        return $this->cachePath .
            '/' . substr($cacheHash, 0, 2) .
            '/' . substr($cacheHash, 2) . '.' . $extension;
    }
}
