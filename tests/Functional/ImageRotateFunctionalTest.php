<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageRotateFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testRotate(): void
    {
        $expectedMd5 = '4ab7ff5065a1ad8aa7d1120f2cb5f7f3';
        $request = $this->generateRequest('http://localhost/assets/r115/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testRotateWithTransparent(): void
    {
        $expectedMd5 = 'd684a7b4c442d7d4c9f1669f54d13f44';
        $request = $this->generateRequest('http://localhost/assets/r115-btransparent/testPNG.png');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testRotateWithBackgroundRed(): void
    {
        $expectedMd5 = '0b7ad69935cf19d5efc2c843f951e4d0';
        $request = $this->generateRequest('http://localhost/assets/r115-bff0000/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
