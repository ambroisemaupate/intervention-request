<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageHotspotFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testRightOnLandscape(): void
    {
        $expectedMd5 = 'b8a58cda05b6365a12d92cce2cc996bb';
        $request = $this->generateRequest('http://localhost/assets/d1x0.5-c1x1-w1050/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testLeftOnLandscape(): void
    {
        $expectedMd5 = 'cfee1ea3019c15d9df49e31212220c61';
        $request = $this->generateRequest('http://localhost/assets/d0x0.5-c1x1-w1050/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testTopOnPortrait(): void
    {
        $expectedMd5 = 'ece62c3321e1b19ddede51d1c328da5f';
        $request = $this->generateRequest('http://localhost/assets/r90-d0.5x0-c1x1-w1000/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testBottomOnPortrait(): void
    {
        $expectedMd5 = '63a51b670494190fc3e208d0ccc1c0e6';
        $request = $this->generateRequest('http://localhost/assets/r90-d0.5x1-c1x1-w1000/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
