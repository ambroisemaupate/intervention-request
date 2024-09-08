<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @package AM\InterventionRequest\Listener
 */
final class TinifyListener implements ImageFileEventSubscriberInterface
{
    private string $apiKey = '';
    private ?LoggerInterface $logger;

    /**
     * @param string $apiKey
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $apiKey, LoggerInterface $logger = null)
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
            $response->headers->set('X-IR-Tinify', '1');
            $event->setResponse($response);
        }
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     * @throws \Tinify\AccountException
     */
    public function onImageSaved(ImageSavedEvent $event): void
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
    public function supports(File $image = null): bool
    {
        return ('' !== $this->apiKey && null !== $image && $image->getPathname() !== '');
    }

    /**
     * @param string $localPath
     * @param \Tinify\Source $source
     * @return void
     */
    protected function overrideImageFile($localPath, \Tinify\Source $source): void
    {
        $source->toFile($localPath);
    }
}
