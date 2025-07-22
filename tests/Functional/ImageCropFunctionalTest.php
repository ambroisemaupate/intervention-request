<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageCropFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = '35a7de410a39f361fa51a46dfbdbb852';
        $request = $this->generateRequest('http://localhost/assets/c1x1/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
