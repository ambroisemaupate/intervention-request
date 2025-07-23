<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageBlurFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = '6d0ede87df3f20ea1fd80c7c139f7178';
        // Here add width in way to reduce time of processing
        $request = $this->generateRequest('http://localhost/assets/l90-w300/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
