<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageSharpenFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = 'c9915c11c2d2b321ca72cada1832999b';
        $request = $this->generateRequest('http://localhost/assets/s100-f1000x1000/testUHD.jpg');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
