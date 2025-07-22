<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageHeightenFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = 'fcc208f0a6366e8441d739955ed1605d';
        $request = $this->generateRequest('http://localhost/assets/h800/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
