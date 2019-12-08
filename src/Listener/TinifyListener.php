<?php
/**
 * Copyright © 2016, Ambroise Maupate
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
 * @file KrakenListener.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Tinify\Source;
use Tinify\Tinify;

/**
 * Class TinifyListener
 * @package AM\InterventionRequest\Listener
 */
class TinifyListener implements ImageEventSubscriberInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * TinifyListener constructor.
     * @param $apiKey
     * @param LoggerInterface $logger
     */
    public function __construct($apiKey, LoggerInterface $logger = null)
    {
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ImageSavedEvent::class => 'onImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event)
    {
        if ($this->supports()) {
            $response = $event->getResponse();
            $response->headers->set('X-IR-Tinify', true);
            $event->setResponse($response);
        }
    }

    /**
     * @param ImageSavedEvent $event
     */
    public function onImageSaved(ImageSavedEvent $event)
    {
        if ($this->supports() && $event->getImageFile()->getPathname()) {
            Tinify::setKey($this->apiKey);
            \Tinify\validate();

            $source = \Tinify\fromFile($event->getImageFile()->getPathname());
            $this->overrideImageFile($event->getImageFile()->getPathname(), $source);
            if (null !== $this->logger) {
                $this->logger->debug("Used tinify.io to minify file.", [
                    'path' => $event->getImageFile()->getPathname()
                ]);
            }
        }
    }

    /**
     * @param Image $image
     * @return bool
     */
    public function supports(Image $image = null)
    {
        return ('' !== $this->apiKey);
    }

    /**
     * @param string $localPath
     * @param Source $source
     */
    protected function overrideImageFile($localPath, Source $source)
    {
        $source->toFile($localPath);
    }
}
