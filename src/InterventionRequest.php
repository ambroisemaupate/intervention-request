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
 * @file InterventionRequest.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest;

use AM\InterventionRequest\Cache\FileCache;
use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Processor as Processor;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class InterventionRequest
{
    protected $request;
    protected $response;
    protected $logger;
    protected $configuration;
    protected $nativeImage;
    protected $image;
    protected $processors;
    protected $quality;

    /**
     * Create a new InterventionRequest object.
     *
     * @param Configuration        $configuration
     * @param Request|null         $request
     * @param LoggerInterface|null $logger
     * @param array|null           $processors
     */
    public function __construct(
        Configuration $configuration,
        Request $request = null,
        LoggerInterface $logger = null,
        array $processors = null
    ) {
        $this->logger = $logger;

        if (null !== $request) {
            $this->request = $request;
        } else {
            $this->request = Request::createFromGlobals();
        }

        $this->configuration = $configuration;
        $this->defineTimezone();

        if (null === $processors) {
            $this->processors = [
                new Processor\RotateProcessor($this->request),
                new Processor\CropResizedProcessor($this->request),
                new Processor\FitProcessor($this->request),
                new Processor\CropProcessor($this->request),
                new Processor\WidenProcessor($this->request),
                new Processor\HeightenProcessor($this->request),
                new Processor\LimitColorsProcessor($this->request),
                new Processor\GreyscaleProcessor($this->request),
                new Processor\ContrastProcessor($this->request),
                new Processor\BlurProcessor($this->request),
                new Processor\SharpenProcessor($this->request),
                new Processor\ProgressiveProcessor($this->request),
            ];
        } elseif (is_array($processors)) {
            $this->processors = $processors;
        }
    }

    private function defineTimezone()
    {
        /*
         * Define a request wide timezone
         */
        date_default_timezone_set($this->configuration->getTimezone());
    }

    /**
     * Handle request to convert it to a Response object.
     */
    public function handle()
    {
        if (!$this->request->query->has('image')) {
            throw new \RuntimeException("No valid image path found in URI", 1);
        }

        $nativePath = $this->configuration->getImagesPath() .
        '/' . $this->request->query->get('image');
        $this->nativeImage = new File($nativePath);
        $this->parseQuality();

        if ($this->configuration->hasCaching()) {

            $cache = new FileCache(
                $this->request,
                $this->nativeImage,
                $this->configuration->getCachePath(),
                $this->logger,
                $this->quality,
                $this->configuration->getTtl(),
                $this->configuration->getGcProbability()
            );
            $this->response = $cache->getResponse(function (InterventionRequest $interventionRequest) {
                return $interventionRequest->processImage();
            }, $this);
        } else {
            $this->processImage();
            $this->response = new Response(
                (string) $this->image->encode(null, $this->quality),
                Response::HTTP_OK,
                [
                    'Content-Type' => $this->image->mime(),
                    'Content-Disposition' => 'filename="' . $this->nativeImage->getFilename() . '"',
                    'X-Generator-First-Render' => true,
                ]
            );
            $this->response->setLastModified(new \DateTime('now'));
        }
    }

    public function processImage()
    {
        // create an image manager instance with favored driver
        $manager = new ImageManager([
            'driver' => $this->configuration->getDriver(),
        ]);

        $this->image = $manager->make($this->nativeImage->getPathname());

        foreach ($this->processors as $processor) {
            $processor->process($this->image);
        }

        return $this->image;
    }

    public function parseQuality()
    {
        if ($this->request->query->has('quality') &&
            1 === preg_match('#^([0-9]+)$#', $this->request->query->get('quality'), $quality)) {

            $quality[1] = (int) $quality[1];

            if ($quality[1] <= 100 &&
                $quality[1] > 0) {
                $this->quality = $quality[1];
            } else {
                $this->quality = $this->configuration->getDefaultQuality();
            }
        } else {
            $this->quality = $this->configuration->getDefaultQuality();
        }

        return $this->quality;
    }

    /**
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        if (null !== $this->response) {
            $this->response->setPublic();
            $this->response->setPrivate();
            $this->response->setMaxAge($this->configuration->getTtl());
            $this->response->setSharedMaxAge($this->configuration->getTtl());
            $this->response->setCharset('UTF-8');
            $this->response->prepare($this->request);

            return $this->response;
        } else {
            throw new \RuntimeException("Request had not been handled. Use handle() method before getResponse()", 1);
        }
    }

    /**
     * Gets the value of request.
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets the value of nativeImage.
     *
     * @return mixed
     */
    public function getNativeImage()
    {
        return $this->nativeImage;
    }

    /**
     * Gets the value of configuration.
     *
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Gets the value of logger.
     *
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
