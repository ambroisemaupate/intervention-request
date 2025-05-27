<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

class ImageCropFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testCrop(): void
    {
        $expectedMd5 = 'b7e99efe3a0e2011afaa1ec2c0b93523';
        $request = $this->generateRequest('http://localhost/assets/c1000x1000/rhino.webp');
        $this->interventionRequest->handleRequest($request);
        $response = $this->interventionRequest->getResponse($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }
}
