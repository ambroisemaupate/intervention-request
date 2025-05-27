<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageHeightenFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testHeighten(): void
    {
        $expectedMd5 = '794e14c1357fe7913dbdbcd56a41b8a6';
        $request = $this->generateRequest('http://localhost/assets/h800/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
