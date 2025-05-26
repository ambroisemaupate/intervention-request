<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageHotspotFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testHotspotRightOnLandscape(): void
    {
        $expectedMd5 = '0d6f771f07af11678db5ef930f7a9f41';
        $request = $this->generateRequest('http://localhost/assets/d1x0.5-c1x1-w1050/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testHotspotLeftOnLandscape(): void
    {
        $expectedMd5 = 'dd9fbc7edcff36fcd14676178a0177cf';
        $request = $this->generateRequest('http://localhost/assets/d0x0.5-c1x1-w1050/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testHotspotTopOnPortrait(): void
    {
        $expectedMd5 = 'b4d2dc2c463f2b456f59cc3672c4904a';
        $request = $this->generateRequest('http://localhost/assets/r90-d0.5x0-c1x1-w1000/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testHotspotBottomOnPortrait(): void
    {
        $expectedMd5 = '175bc011be1af8dfd5231ce714be0ffb';
        $request = $this->generateRequest('http://localhost/assets/r90-d0.5x1-c1x1-w1000/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
