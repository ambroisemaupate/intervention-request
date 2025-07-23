<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class TraceListener implements EventSubscriberInterface
{
    public function __construct(private bool $debug)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => ['onRequest', 1000]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->debug) {
            return;
        }

        if ($event->getRequest()->query->has('trace')) {
            $event->getRequest()->attributes->set('trace', true);
        }
    }
}
