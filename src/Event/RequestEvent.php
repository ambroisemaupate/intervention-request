<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use AM\InterventionRequest\InterventionRequest;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestEvent extends Event
{
    protected Request $request;
    protected InterventionRequest $interventionRequest;
    protected ?Response $response = null;
    protected int $quality;

    public function __construct(Request $request, InterventionRequest $interventionRequest, int $quality = 90)
    {
        $this->request = $request;
        $this->interventionRequest = $interventionRequest;
        $this->quality = $quality;
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
