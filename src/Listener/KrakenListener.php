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
 * @package AM\InterventionRequest\Listener
 */
final class KrakenListener implements ImageFileEventSubscriberInterface
{
    private string $apiKey;
    private string $apiSecret;
    private ?LoggerInterface $logger;
    private bool $lossy;
    /**
     * @var \Kraken
     */
    private $kraken;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param bool $lossy
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $apiKey, string $apiSecret, bool $lossy = true, LoggerInterface $logger = null)
    {
        if (!class_exists('\Kraken')) {
            throw new \RuntimeException('kraken-io/kraken-php library is required to use KrakenListener');
        }
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->logger = $logger;
        $this->lossy = $lossy;

        $this->kraken = new \Kraken($this->apiKey, $this->apiSecret);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
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
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ($this->supports() && (bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Kraken', '1');
            $event->setResponse($response);
        }
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
            $params = array(
                "file" => $event->getImageFile()->getPathname(),
                "wait" => true,
                "lossy" => $this->lossy,
            );

            $data = $this->kraken->upload($params);

            if (isset($data["success"]) && !empty($data['kraked_url'])) {
                if (null !== $this->logger) {
                    $this->logger->debug("Used kraken.io to minify file.", $data);
                }
                $this->overrideImageFile($event->getImageFile()->getPathname(), $data['kraked_url']);
            } else {
                return;
            }
        }
    }

    /**
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null): bool
    {
        return null !== $this->kraken &&
            '' !== $this->apiKey &&
            '' !== $this->apiSecret &&
            null !== $image &&
            $image->getPathname() !== '';
    }

    /**
     * @param string $localPath
     * @param string $krakedUrl
     * @return void
     */
    protected function overrideImageFile(string $localPath, string $krakedUrl): void
    {
        /**
         * Initialize the cURL session
         */
        $ch = curl_init();
        /**
         * Set the URL of the page or file to download.
         */
        curl_setopt($ch, CURLOPT_URL, $krakedUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /**
         * Create a new file
         */
        $fp = fopen($localPath, 'w');
        if (false !== $fp) {
            /**
             * Ask cURL to write the contents to a file
             */
            curl_setopt($ch, CURLOPT_FILE, $fp);
            /**
             * Execute the cURL session
             */
            curl_exec($ch);
            /**
             * Close cURL session and file
             */
            curl_close($ch);
            fclose($fp);
        }
    }
}
