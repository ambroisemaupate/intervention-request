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

    /**
     * @param Image|null $image Read-only image
     */
    public function __construct(private Response $response, ?Image $image = null)
    {
        parent::__construct($image);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): ResponseEvent
    {
        $this->response = $response;

        return $this;
    }
}
