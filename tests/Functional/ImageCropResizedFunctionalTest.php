<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageCropResizedFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testCropResizedCenter(): void
    {
        $expectedMd5 = 'a6db085d13aed1b77a8e7f22b5275d7a';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w1000-ac/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testCropResizedLeft(): void
    {
        $expectedMd5 = 'f1db8ff76a414daf8119f24fc54511aa';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w1000-al/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testCropResizedRight(): void
    {
        $expectedMd5 = '0716abe845f137c308e49c00d5abea17';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w1000-ar/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testCropResizedOversized(): void
    {
        $expectedMd5 = '35a7de410a39f361fa51a46dfbdbb852';
        $request = $this->generateRequest('http://localhost/assets/c1x1-w9000-ac/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
