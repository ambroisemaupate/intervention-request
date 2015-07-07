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
 * @file FileCache.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Cache;

use AM\InterventionRequest\InterventionRequest;
use Closure;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileCache
{
    protected $request;
    protected $response;
    protected $realImage;
    protected $cacheFilePath;
    protected $cacheFile;
    protected $cachePath;
    protected $logger;
    protected $quality;
    protected $ttl;
    protected $gcProbability;

    protected static $allowedExtensions = array(
        'jpeg',
        'jpg',
        'gif',
        'tiff',
        'png',
        'psd',
    );

    /**
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Component\HttpFoundation\File\File $realImage
     * @param string $cachePath
     * @param Psr\Log\LoggerInterface|null $logger
     * @param integer $quality
     * @param integer $ttl
     * @param boolean $useFileMd5
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
        $this->cachePath = $cachePath;
        $this->logger = $logger;
        $this->realImage = $realImage;
        $this->quality = $quality;
        $this->ttl = $ttl;
        $this->gcProbability = $gcProbability;

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
        '.' . $this->getExtension();
    }

    protected function getExtension()
    {
        $extension = 'jpg';
        if (in_array(strtolower($this->realImage->getExtension()), static::$allowedExtensions)) {
            $extension = strtolower($this->realImage->getExtension());
        }

        return $extension;
    }

    /**
     * @param Intervention\Image\Image $image
     */
    public function saveImage(Image $image)
    {
        $path = dirname($this->cacheFilePath);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $image->save($this->cacheFilePath, $this->quality);
    }

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
        } catch (FileNotFoundException $e) {
            if (is_callable($callback)) {
                $image = $callback($interventionRequest);
                $this->saveImage($image);

                // send HTTP header and output image data
                $response = new Response(
                    (string) $image->encode(null, $this->quality),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => $image->mime(),
                        'Content-Disposition' => 'filename="' . $this->realImage->getFilename() . '"',
                        'X-Generator-First-Render' => true,
                    ]
                );
                $response->setLastModified(new \DateTime('now'));

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
}
