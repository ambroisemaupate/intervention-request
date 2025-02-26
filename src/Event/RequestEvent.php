<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use AM\InterventionRequest\InterventionRequest;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestEvent extends Event
{
    protected ?Response $response = null;

    public function __construct(
        protected readonly Request $request,
        protected readonly InterventionRequest $interventionRequest,
        protected int $quality = 90
    ) {
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     *
     * @return RequestEvent
     */
    public function setResponse(Response $response): RequestEvent
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return InterventionRequest
     */
    public function getInterventionRequest(): InterventionRequest
    {
        return $this->interventionRequest;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     *
     * @return RequestEvent
     */
    public function setQuality(int $quality): RequestEvent
    {
        $this->quality = $quality;

        return $this;
    }
}
