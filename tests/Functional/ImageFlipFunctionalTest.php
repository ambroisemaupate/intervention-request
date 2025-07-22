<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageFlipFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testHorizontal(): void
    {
        $expectedMd5 = '52d0e88a82b1fd5b7a5356f412b27f7b';
        $request = $this->generateRequest('http://localhost/assets/mh/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testVertical(): void
    {
        $expectedMd5 = '65bd1c3dcbc120e6a2b876b37e59d490';
        $request = $this->generateRequest('http://localhost/assets/mv/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
