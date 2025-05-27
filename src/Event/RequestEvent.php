<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use AM\InterventionRequest\InterventionRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

final class RequestEvent extends Event
{
    protected ?Response $response = null;

    public function __construct(
        protected readonly Request $request,
        protected readonly InterventionRequest $interventionRequest,
        protected int $quality = 90,
        protected bool $progressive = false,
    ) {
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): RequestEvent
    {
        $this->response = $response;

        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getInterventionRequest(): InterventionRequest
    {
        return $this->interventionRequest;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): RequestEvent
    {
        $this->quality = $quality;

        return $this;
    }

    public function isProgressive(): bool
    {
        return $this->progressive;
    }

    public function setProgressive(bool $progressive): RequestEvent
    {
        $this->progressive = $progressive;

        return $this;
    }
}
