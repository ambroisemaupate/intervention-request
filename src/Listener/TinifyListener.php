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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class TinifyListener
 *
 * @package AM\InterventionRequest\Listener
 */
class TinifyListener implements ImageFileEventSubscriberInterface
{
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param string $apiKey
     * @param LoggerInterface|null $logger
     */
    public function __construct($apiKey, LoggerInterface $logger = null)
    {
        if (!class_exists('\Tinify\Tinify')) {
            throw new \RuntimeException('tinify/tinify library is required to use TinifyListener');
        }
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

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->supports() && (bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Tinify', '1');
            $event->setResponse($response);
        }
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     * @throws \Tinify\AccountException
     */
    public function onImageSaved(ImageSavedEvent $event)
    {
        if ($this->supports($event->getImageFile())) {
            \Tinify\Tinify::setKey($this->apiKey);
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
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null)
    {
        return ('' !== $this->apiKey && null !== $image && $image->getPathname() !== '');
    }

    /**
     * @param string $localPath
     * @param \Tinify\Source $source
     * @return void
     */
    protected function overrideImageFile($localPath, \Tinify\Source $source)
    {
        $source->toFile($localPath);
    }
}
