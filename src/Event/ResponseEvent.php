<?php

/**
 * Copyright © 2017, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file ResponseEvent.php
 * @author Ambroise Maupate
 */

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
