<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ResponseHeadersListener implements EventSubscriberInterface
{
    public function __construct(private Configuration $configuration, private bool $debug = false)
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

        if (!$this->debug && $response->isCacheable()) {
            $response->setPublic();
            $response->setMaxAge($this->configuration->getResponseTtl());
            $response->setSharedMaxAge($this->configuration->getResponseTtl());
        } else {
            $response->setPrivate();
            $response->setMaxAge(0);
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

        if ($this->debug) {
            $response->headers->set('X-Debug', '1');
        }

        $event->setResponse($response);
    }
}
