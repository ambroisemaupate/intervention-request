<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageBackgroundFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = 'b53e49a0db123ebbaf5540c5fa1d1997';
        $request = $this->generateRequest('http://localhost/assets/bff0000/testPNG.png');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
