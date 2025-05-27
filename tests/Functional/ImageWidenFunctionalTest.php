<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageWidenFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testWiden(): void
    {
        $expectedMd5 = 'b5dc420b0e12564ad477a04248726abe';
        $request = $this->generateRequest('http://localhost/assets/w500/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
