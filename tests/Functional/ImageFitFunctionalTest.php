<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageFitFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function test(): void
    {
        $expectedMd5 = 'a543141705444fe939f60aa68f013dd7';
        $request = $this->generateRequest('http://localhost/assets/f500x500/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
