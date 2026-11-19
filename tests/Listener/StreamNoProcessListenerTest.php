<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Listener;

use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\FlysystemFileResolver;
use AM\InterventionRequest\Listener\StreamNoProcessListener;
use AM\InterventionRequest\Tests\Functional\InterventionRequestTestCase;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StreamNoProcessListenerTest extends InterventionRequestTestCase
{
    private const FILE = 'test.avif';

    private function stream(?string $range): Response
    {
        $imagesPath = dirname(__DIR__, 2).'/public/images';
        $listener = new StreamNoProcessListener(new FlysystemFileResolver(
            new Filesystem(new LocalFilesystemAdapter($imagesPath)),
            new NullLogger(),
            sys_get_temp_dir(),
        ));
        $request = Request::create('/?image='.self::FILE.'&no_process=1');
        if (null !== $range) {
            $request->headers->set('Range', $range);
        }
        $event = new RequestEvent($request, $this->interventionRequest);
        $listener->onRequest($event);
        $response = $event->getResponse();
        $this->assertNotNull($response);

        return $response;
    }

    private function body(Response $response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }

    public function testFullFile(): void
    {
        $content = (string) file_get_contents(dirname(__DIR__, 2).'/public/images/'.self::FILE);
        $response = $this->stream(null);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame((string) strlen($content), $response->headers->get('Content-Length'));
        $this->assertSame('bytes', $response->headers->get('Accept-Ranges'));
        $this->assertMatchesRegularExpression('/^\w{3}, \d{2} \w{3} \d{4} \d{2}:\d{2}:\d{2} GMT$/', (string) $response->headers->get('Last-Modified'));
        $this->assertSame($content, $this->body($response));
    }

    public function testByteRange(): void
    {
        $content = (string) file_get_contents(dirname(__DIR__, 2).'/public/images/'.self::FILE);
        $size = strlen($content);

        $response = $this->stream('bytes=10-19');
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('10', $response->headers->get('Content-Length'));
        $this->assertSame('bytes 10-19/'.$size, $response->headers->get('Content-Range'));
        $this->assertSame(substr($content, 10, 10), $this->body($response));

        $response = $this->stream('bytes=0-');
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame((string) $size, $response->headers->get('Content-Length'));

        $response = $this->stream('bytes=-5');
        $this->assertSame('bytes '.($size - 5).'-'.($size - 1).'/'.$size, $response->headers->get('Content-Range'));
        $this->assertSame(substr($content, -5), $this->body($response));
    }

    public function testUnsatisfiableRange(): void
    {
        $size = filesize(dirname(__DIR__, 2).'/public/images/'.self::FILE);
        $response = $this->stream('bytes='.$size.'-');

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame('bytes */'.$size, $response->headers->get('Content-Range'));
    }
}
