<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageWidenFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = 'd06c62e0afa34ba9e2ac9f3fd71ea6dc';
        $request = $this->generateRequest('http://localhost/assets/w500/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
