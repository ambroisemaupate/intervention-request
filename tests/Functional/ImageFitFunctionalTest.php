<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageFitFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testFit(): void
    {
        $expectedMd5 = 'f19703e93d053f41408503f6377f7cb6';
        $request = $this->generateRequest('http://localhost/assets/f500x500/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
