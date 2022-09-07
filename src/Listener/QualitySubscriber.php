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
        $this->quality = intval($requestEvent->getRequest()->get(
            'quality',
            $requestEvent->getInterventionRequest()->getConfiguration()->getDefaultQuality()
        ));

        if ($requestEvent->getRequest()->query->has('no_process')) {
            // Do not alter quality at image file save. but allow post-process optimizer to use quality info.
            $requestEvent->setQuality(100);
        } else {
            if ($this->quality <= 100 && $this->quality > 0) {
                $requestEvent->setQuality($this->quality);
            } else {
                $requestEvent->setQuality(
                    $requestEvent->getInterventionRequest()->getConfiguration()->getDefaultQuality()
                );
            }
        }
    }

    /**
     * @param ImageSavedEvent $imageSavedEvent
     * @return void
     */
    public function onImageSaved(ImageSavedEvent $imageSavedEvent): void
    {
        if ($this->quality <= 100 && $this->quality > 0) {
            $imageSavedEvent->setQuality($this->quality);
        }
    }
}
