<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Event;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Response;

final class ResponseEvent extends ImageEvent
{
    /**
     * @deprecated ResponseEvent::class
     */
    public const NAME = ResponseEvent::class;

    private Response $response;

    /**
     * @param Response $response
     * @param Image|null $image Read-only image
     */
    public function __construct(Response $response, Image $image = null)
    {
        parent::__construct($image);
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     * @return ResponseEvent
     */
    public function setResponse(Response $response): ResponseEvent
    {
        $this->response = $response;
        return $this;
    }
}
