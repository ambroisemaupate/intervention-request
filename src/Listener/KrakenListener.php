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

/**
 * Class KrakenListener
 * @package AM\InterventionRequest\Listener
 */
class KrakenListener implements ImageEventSubscriberInterface
{
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $apiSecret;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $lossy;

    /**
     * @var \Kraken
     */
    private $kraken;

    /**
     * KrakenListener constructor.
     * @param string $apiKey
     * @param string $apiSecret
     * @param bool $lossy
     * @param LoggerInterface $logger
     */
    public function __construct($apiKey, $apiSecret, $lossy = true, LoggerInterface $logger = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->logger = $logger;
        $this->lossy = $lossy;

        $this->kraken = new \Kraken($this->apiKey, $this->apiSecret);
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
        $response = $event->getResponse();
        if ($this->supports() && (bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Kraken', true);
            $event->setResponse($response);
        }
    }

    public function onImageSaved(ImageSavedEvent $event)
    {
        if ($this->supports() && $event->getImageFile()->getPathname()) {
            $params = array(
                "file" => $event->getImageFile()->getPathname(),
                "wait" => true,
                "lossy" => $this->lossy,
            );

            $data = $this->kraken->upload($params);

            if ($data["success"] && !empty($data['kraked_url'])) {
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
     * @param Image $image
     * @return bool
     */
    public function supports(Image $image = null)
    {
        return (null !== $this->kraken && '' !== $this->apiKey && '' !== $this->apiSecret);
    }

    /**
     * @param string $localPath
     * @param string $krakedUrl
     */
    protected function overrideImageFile($localPath, $krakedUrl)
    {
        /**
         * Initialize the cURL session
         */
        $ch = curl_init();
        /**
         * Set the URL of the page or file to download.
         */
        curl_setopt($ch, CURLOPT_URL, $krakedUrl);
        /**
         * Create a new file
         */
        $fp = fopen($localPath, 'w');
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
