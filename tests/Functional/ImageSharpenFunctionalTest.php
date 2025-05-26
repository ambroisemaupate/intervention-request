<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageSharpenFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testSharpen(): void
    {
        $expectedMd5 = 'cb35dd342d3b76f75eea9634974e6f84';
        $request = $this->generateRequest('http://localhost/assets/s100-f1000x1000/testUHD.jpg');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
