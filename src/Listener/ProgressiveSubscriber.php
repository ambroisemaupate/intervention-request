<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProgressiveSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private bool $progressive,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 100],
            ImageSavedEvent::class => ['onImageSaved', 100],
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-IR-Progressive', (string) $this->progressive);
        $event->setResponse($response);
    }

    public function onRequest(RequestEvent $requestEvent): void
    {
        if ($requestEvent->getRequest()->query->has('no_process')) {
            // Do not alter progressive at image file save. but allow to post-process optimizer to use progressive info.
            $this->progressive = false;
            $requestEvent->setProgressive(false);
        } else {
            $progressive = $requestEvent->getRequest()->get(
                'progressive',
                $this->progressive
            );
            if (\is_numeric($progressive)) {
                if (1 === (int) $progressive) {
                    $progressive = true;
                } elseif (0 === (int) $progressive) {
                    $progressive = false;
                } else {
                    throw new \InvalidArgumentException('Progressive must be 1 or 0');
                }
            }
            if (\is_bool($progressive)) {
                $this->progressive = $progressive;
                $requestEvent->setProgressive($this->progressive);
            }
        }
    }

    public function onImageSaved(ImageSavedEvent $imageSavedEvent): void
    {
        $imageSavedEvent->setProgressive($this->progressive);
    }
}
