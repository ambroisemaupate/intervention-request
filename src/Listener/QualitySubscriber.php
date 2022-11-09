<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class QualitySubscriber implements EventSubscriberInterface
{
    private int $quality;

    /**
     * @param int $quality
     */
    public function __construct(int $quality)
    {
        $this->setQuality($quality);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 100],
            ImageSavedEvent::class => ['onImageSaved', 100],
            ResponseEvent::class => 'onResponse',
        ];
    }

    /**
     * @param int $quality
     * @return QualitySubscriber
     */
    public function setQuality(int $quality): QualitySubscriber
    {
        if ($quality > 100 || $quality <= 0) {
            throw new \InvalidArgumentException('Quality must be between 1 and 100');
        }
        $this->quality = $quality;
        return $this;
    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-IR-Quality', (string) $this->quality);
        $event->setResponse($response);
    }

    /**
     * @param RequestEvent $requestEvent
     * @return void
     */
    public function onRequest(RequestEvent $requestEvent): void
    {
        if ($requestEvent->getRequest()->query->has('no_process')) {
            // Do not alter quality at image file save. but allow to post-process optimizer to use quality info.
            $this->setQuality(100);
            $requestEvent->setQuality(100);
        } else {
            $this->setQuality(intval($requestEvent->getRequest()->get(
                'quality',
                $this->quality
            )));
            $requestEvent->setQuality($this->quality);
        }
    }

    /**
     * @param ImageSavedEvent $imageSavedEvent
     * @return void
     */
    public function onImageSaved(ImageSavedEvent $imageSavedEvent): void
    {
        $imageSavedEvent->setQuality($this->quality);
    }
}
