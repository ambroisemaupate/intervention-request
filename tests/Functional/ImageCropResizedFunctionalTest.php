<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageCropResizedFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testCenter(): void
    {
        $expectedMd5 = 'ac9734bc90e15427351bf2c7dbdcb98c';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w1000-ac/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testLeft(): void
    {
        $expectedMd5 = '51b5ee1a43724637f28d93e18ad8e83c';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w1000-al/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testRight(): void
    {
        $expectedMd5 = '8ec72219a8a28eb2bb326a9c9d595dad';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w1000-ar/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testOversized(): void
    {
        $expectedMd5 = '35a7de410a39f361fa51a46dfbdbb852';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w9000-ac/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
