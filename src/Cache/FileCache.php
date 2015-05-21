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

use Closure;
use AM\InterventionRequest\InterventionRequest;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
/**
*
*/
class FileCache
{
    protected $interventionRequest;
    protected $request;
    protected $response;
    protected $realImage;
    protected $cacheFile;
    protected $cachePath;
    protected $quality = 90;

    function __construct(InterventionRequest $interventionRequest)
    {
        $this->interventionRequest = $interventionRequest;
        $this->request = $interventionRequest->getRequest();
        $this->realImage = $interventionRequest->getNativeImage();
        $this->quality = $interventionRequest->parseQuality();

        $cacheHash = hash('crc32b', serialize($this->request->query->all()));
        $this->cachePath = $this->interventionRequest->getConfiguration()->getCachePath() .
                                '/' . implode('/', str_split($cacheHash, 2)) .
                                '.' . $this->realImage->getExtension();
    }

    public function saveImage(Image $image)
    {
        $path = dirname($this->cachePath);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $image->save($this->cachePath, $this->quality);
    }

    public function getResponse(Closure $callback)
    {
        try {
            $this->cacheFile = new File($this->cachePath);

            $response = new Response(
                file_get_contents($this->cacheFile->getPathname()),
                Response::HTTP_OK,
                [
                    'Content-Type' => $this->cacheFile->getMimeType(),
                    'Content-Disposition' => 'filename="'.$this->realImage->getFilename().'"',
                    'X-Generator-Cached' => true,
                ]
            );
        } catch (FileNotFoundException $e) {
            if (is_callable($callback)) {
                $image = $callback($this->interventionRequest);
                $this->saveImage($image);

                // send HTTP header and output image data
                $response = new Response(
                    (string) $image->encode(null, $this->quality),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => $image->mime(),
                        'Content-Disposition' => 'filename="'.$this->realImage->getFilename().'"',
                        'X-Generator-First-Render' => true,
                    ]
                );
                $response->setLastModified(new \DateTime('now'));

            } else {
                throw new \RuntimeException("No image handle closure defined", 1);
            }
        }

        return $response;
    }
}