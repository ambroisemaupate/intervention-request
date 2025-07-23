<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageRotateFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = 'ce3f33e05820b8409a0a6c0e86c4eee7';
        $request = $this->generateRequest('http://localhost/assets/r115/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testWithTransparent(): void
    {
        $expectedMd5 = '51b5d67ce0d7bbb2bb442181f6a7f521';
        $request = $this->generateRequest('http://localhost/assets/r115-btransparent/testPNG.png');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testWithBackgroundRed(): void
    {
        $expectedMd5 = '4121cc9b35f4149a18e543a542a7915d';
        $request = $this->generateRequest('http://localhost/assets/r115-bff0000/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
