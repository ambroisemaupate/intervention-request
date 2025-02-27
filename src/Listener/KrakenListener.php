<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

final class KrakenListener implements ImageFileEventSubscriberInterface
{
    private \Kraken $kraken;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly bool $lossy = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
        if (!class_exists('\Kraken')) {
            throw new \RuntimeException('kraken-io/kraken-php library is required to use KrakenListener');
        }

        $this->kraken = new \Kraken($this->apiKey, $this->apiSecret);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ($this->supports() && (bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Kraken', '1');
            $event->setResponse($response);
        }
    }

    public function onImageSaved(ImageSavedEvent $event): void
    {
        if (!$this->supports($event->getImage(), $event->getImageFile())) {
            return;
        }
        $params = [
            'file' => $event->getImageFile()->getPathname(),
            'wait' => true,
            'lossy' => $this->lossy,
        ];

        $data = $this->kraken->upload($params);

        if (!is_array($data)) {
            return;
        }

        if (isset($data['success']) && is_string($data['kraked_url']) && !empty($data['kraked_url'])) {
            if (null !== $this->logger) {
                $this->logger->debug('Used kraken.io to minify file.', $data);
            }
            $this->overrideImageFile($event->getImageFile()->getPathname(), $data['kraked_url']);
        }
    }

    public function supports(?Image $image = null, ?File $file = null): bool
    {
        return '' !== $this->apiKey
            && '' !== $this->apiSecret
            && null !== $file
            && '' !== $file->getPathname();
    }

    /**
     * @param non-empty-string $krakedUrl
     */
    protected function overrideImageFile(string $localPath, string $krakedUrl): void
    {
        /**
         * Initialize the cURL session.
         */
        $ch = curl_init();
        /*
         * Set the URL of the page or file to download.
         */
        curl_setopt($ch, CURLOPT_URL, $krakedUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /**
         * Create a new file.
         */
        $fp = fopen($localPath, 'w');
        if (false !== $fp) {
            /*
             * Ask cURL to write the contents to a file
             */
            curl_setopt($ch, CURLOPT_FILE, $fp);
            /*
             * Execute the cURL session
             */
            curl_exec($ch);
            /*
             * Close cURL session and file
             */
            curl_close($ch);
            fclose($fp);
        }
    }
}
