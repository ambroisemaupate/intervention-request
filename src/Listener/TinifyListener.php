<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class TinifyListener implements ImageFileEventSubscriberInterface
{
    public function __construct(private string $apiKey = '', private ?LoggerInterface $logger = null)
    {
        if (!class_exists('\Tinify\Tinify')) {
            throw new \RuntimeException('tinify/tinify library is required to use TinifyListener');
        }
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
            $response->headers->set('X-IR-Tinify', '1');
            $event->setResponse($response);
        }
    }

    /**
     * @throws \Tinify\AccountException
     */
    public function onImageSaved(ImageSavedEvent $event): void
    {
        if (!$this->supports($event->getImage(), $event->getImageFile())) {
            return;
        }

        \Tinify\Tinify::setKey($this->apiKey);
        \Tinify\validate();

        /** @var \Tinify\Source $source */
        $source = \Tinify\fromFile($event->getImageFile()->getPathname());
        $this->overrideImageFile($event->getImageFile()->getPathname(), $source);
        if (null !== $this->logger) {
            $this->logger->debug('Used tinify.io to minify file.', [
                'path' => $event->getImageFile()->getPathname(),
            ]);
        }
    }

    public function supports(?Image $image = null, ?File $file = null): bool
    {
        return '' !== $this->apiKey && null !== $file && '' !== $file->getPathname();
    }

    protected function overrideImageFile(string $localPath, \Tinify\Source $source): void
    {
        $source->toFile($localPath);
    }
}
