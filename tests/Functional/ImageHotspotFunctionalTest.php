<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;

class ImageHotspotFunctionalTest extends InterventionRequestTestCase
{
    /**
     * @throws \Exception
     */
    public function testRightOnLandscape(): void
    {
        $expectedMd5 = 'b8a58cda05b6365a12d92cce2cc996bb';
        $request = $this->generateRequest('http://localhost/assets/d1x0.5-c1x1-w1050/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testLeftOnLandscape(): void
    {
        $expectedMd5 = 'cfee1ea3019c15d9df49e31212220c61';
        $request = $this->generateRequest('http://localhost/assets/d0x0.5-c1x1-w1050/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testTopOnPortrait(): void
    {
        $expectedMd5 = 'ece62c3321e1b19ddede51d1c328da5f';
        $request = $this->generateRequest('http://localhost/assets/r90-d0.5x0-c1x1-w1000/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @throws \Exception
     */
    public function testBottomOnPortrait(): void
    {
        $expectedMd5 = '63a51b670494190fc3e208d0ccc1c0e6';
        $request = $this->generateRequest('http://localhost/assets/r90-d0.5x1-c1x1-w1000/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);
        $actualMd5 = $this->getResponseFileMd5($response);

        $this->assertEquals($expectedMd5, $actualMd5);
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * rhino.webp is 2500x1667: area 0.25 to 0.75 is rows 417 to 1250.
     *
     * @see https://github.com/ambroisemaupate/intervention-request/issues/35
     *
     * @throws \Exception
     */
    public function testAreaWithoutCrop(): void
    {
        $expectedMd5 = '50698fb13af5fa1127e44e35490dcee5';
        $request = $this->generateRequest('http://localhost/assets/q75-d0x0x0x0.25x1x0.75/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);

        $this->assertSame([2500, 833], $this->getResponseImageSize($response));
        $this->assertEquals($expectedMd5, $this->getResponseFileMd5($response));
        $this->assertEquals(1, $response->headers->get('X-IR-First-Gen'));
    }

    /**
     * @return iterable<string, array{string, array{int, int}}>
     */
    public static function sizeProvider(): iterable
    {
        // rhino.webp is 2500x1667
        yield 'point alone keeps image' => ['d0.3x0.7', [2500, 1667]];
        yield 'explicit full area keeps image' => ['d0.5x0.5x0x0x1x1', [2500, 1667]];
        yield 'empty area is ignored' => ['d0.5x0.5x0.5x0.5x0.5x0.5', [2500, 1667]];
        yield 'inverted area is ignored' => ['d0.5x0.5x0.75x0.75x0.25x0.25', [2500, 1667]];
        yield 'left half' => ['d0x0x0x0x0.5x1', [1250, 1667]];
        // 0.5px start would round up with size and overflow the image by 1px (black column)
        yield 'half-pixel start does not overflow' => ['d0x0x0.0002x0x1x1', [2499, 1667]];
        // center point is ignored without crop
        yield 'center does not move area' => ['d1x1x0x0.25x1x0.75', [2500, 833]];
        // fit runs before hotspot: area is ignored
        yield 'fit ignores area' => ['f100x100-d0x0x0x0.25x1x0.75', [100, 100]];
        // rotate runs before hotspot: area is relative to the rotated 1667x2500 image
        yield 'rotate then area' => ['r90-d0x0x0x0x1x0.5', [1667, 1250]];
        // width/height run after hotspot: area 2500x833 is resized
        yield 'area then width' => ['d0x0x0x0.25x1x0.75-w500', [500, 167]];
        yield 'area then height' => ['d0x0x0.25x0x0.75x1-h400', [300, 400]];
        // crop ratio is applied inside the area (1250x834)
        yield 'area with square crop' => ['d0.5x0.5x0.25x0.25x0.75x0.75-c1x1', [834, 834]];
        yield 'area with wide crop' => ['d0.5x0.5x0x0x0.5x1-c2x1', [1250, 625]];
    }

    /**
     * @param array{int, int} $expectedSize
     *
     * @throws \Exception
     */
    #[DataProvider('sizeProvider')]
    public function testHotspotSize(string $shortcuts, array $expectedSize): void
    {
        $request = $this->generateRequest('http://localhost/assets/'.$shortcuts.'/rhino.webp');
        $response = $this->interventionRequest->handleRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedSize, $this->getResponseImageSize($response));
    }
}
