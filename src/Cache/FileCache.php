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
 * @file FileCache.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Cache;

use AM\InterventionRequest\Encoder\ImageEncoder;
use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\InterventionRequest;
use Closure;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileCache
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var File
     */
    protected $realImage;
    /**
     * @var string
     */
    protected $cacheFilePath;
    /**
     * @var File
     */
    protected $cacheFile;
    /**
     * @var string
     */
    protected $cachePath;
    /**
     * @var null|LoggerInterface
     */
    protected $logger;
    /**
     * @var int
     */
    protected $quality;
    /**
     * @var int
     */
    protected $ttl;
    /**
     * @var int
     */
    protected $gcProbability;
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    protected static $allowedExtensions = array(
        'jpeg',
        'jpg',
        'gif',
        'tiff',
        'png',
        'psd',
    );
    /**
     * @var ImageEncoder
     */
    private $imageEncoder;

    /**
     * FileCache constructor.
     * @param Request $request
     * @param File $realImage
     * @param $cachePath
     * @param LoggerInterface|null $logger
     * @param int $quality
     * @param int $ttl
     * @param int $gcProbability
     * @param bool $useFileChecksum
     */
    public function __construct(
        Request $request,
        File $realImage,
        $cachePath,
        LoggerInterface $logger = null,
        $quality = 90,
        $ttl = 604800,
        $gcProbability = 300,
        $useFileChecksum = false
    ) {
        $this->request = $request;
        $this->cachePath = realpath($cachePath);
        $this->logger = $logger;
        $this->realImage = $realImage;
        $this->quality = $quality;
        $this->ttl = $ttl;
        $this->gcProbability = $gcProbability;
        $this->imageEncoder = new ImageEncoder();

        /*
         * Get file MD5 to check real image integrity
         */
        if ($useFileChecksum === true) {
            $fileMd5 = hash_file('adler32', $this->realImage->getPathname());
        } else {
            $fileMd5 = '';
        }

        /*
         * Generate a unique cache hash key
         * which will be used as image path
         *
         * The key vary on request param and file md5
         * if enabled.
         */
        $cacheHash = hash('crc32b', serialize($this->request->query->all()) . $fileMd5);

        $this->cacheFilePath = $cachePath .
        '/' . implode('/', str_split($cacheHash, 2)) .
        '.' . $this->imageEncoder->getImageAllowedExtension($this->realImage->getRealPath());
    }

    /**
     * @param Image $image
     * @return Image
     */
    public function saveImage(Image $image)
    {
        $path = dirname($this->cacheFilePath);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $this->imageEncoder->save($image, $this->cacheFilePath, $this->quality);
    }

    /**
     * @param Closure $callback
     * @param InterventionRequest $interventionRequest
     * @return Response
     */
    public function getResponse(Closure $callback, InterventionRequest $interventionRequest)
    {
        try {
            $this->cacheFile = new File($this->cacheFilePath);
            $response = new Response(
                file_get_contents($this->cacheFile->getPathname()),
                Response::HTTP_OK,
                [
                    'Content-Type' => $this->cacheFile->getMimeType(),
                    'Content-Disposition' => 'filename="' . $this->realImage->getFilename() . '"',
                    'X-Generator-Cached' => true,
                ]
            );
            $response->setLastModified(new \DateTime(date("Y-m-d H:i:s", $this->cacheFile->getMTime())));
        } catch (FileNotFoundException $e) {
            if (is_callable($callback)) {
                $image = $callback($interventionRequest);
                if ($image instanceof Image) {
                    $this->saveImage($image);
                    $this->cacheFile = new File($this->cacheFilePath);

                    if (null !== $this->dispatcher) {
                        // create the ImageSavedEvent and dispatch it
                        $event = new ImageSavedEvent($image, $this->cacheFile);
                        $this->dispatcher->dispatch($event);
                    }

                    // send HTTP header and output image data
                    $response = new Response(
                        file_get_contents($this->cacheFile->getPathname()),
                        Response::HTTP_OK,
                        [
                            'Content-Type' => $image->mime(),
                            'Content-Disposition' => 'filename="' . $this->realImage->getFilename() . '"',
                            'X-Generator-First-Render' => true,
                        ]
                    );
                    $response->setLastModified(new \DateTime('now'));
                } else {
                    throw new \RuntimeException("Image is not a valid InterventionImage instance.", 1);
                }
            } else {
                throw new \RuntimeException("No image handle closure defined", 1);
            }
        }

        $this->initializeGarbageCollection();

        return $response;
    }
    /**
     * Determines if the garbage collector should run for this request.
     *
     * @return boolean
     */
    private function garbageCollectionShouldRun()
    {
        if (true === (boolean) $this->request->query->get('force_gc')) {
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
     * @return void
     */
    private function initializeGarbageCollection()
    {
        if ($this->garbageCollectionShouldRun()) {
            $garbageCollector = new GarbageCollector($this->cachePath, $this->logger);
            $garbageCollector->setTtl($this->ttl);
            $garbageCollector->launch();
        }
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     * @return FileCache
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }
}
