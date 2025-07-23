<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageContrastFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = '93d5cffe79f40818547f3e2605793ec2';
        $request = $this->generateRequest('http://localhost/assets/k90/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
