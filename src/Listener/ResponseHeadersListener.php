<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ResponseHeadersListener implements EventSubscriberInterface
{
    public function __construct(private Configuration $configuration)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        if ($response->isCacheable()) {
            $response->setPublic();
            $response->setMaxAge($this->configuration->getResponseTtl());
            $response->setSharedMaxAge($this->configuration->getResponseTtl());
        }
        $response->setCharset('UTF-8');
        $response->headers->set(
            'access-control-allow-headers',
            'DNT,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range'
        );
        $response->headers->set(
            'access-control-allow-methods',
            'GET, OPTIONS'
        );
        $response->headers->set(
            'access-control-allow-origin',
            '*'
        );

        $event->setResponse($response);
    }
}
