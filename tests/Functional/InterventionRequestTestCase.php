<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Tests\Functional;

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Encoder\ImageEncoder;
use AM\InterventionRequest\InterventionRequest;
use AM\InterventionRequest\LocalFileResolver;
use AM\InterventionRequest\ShortUrlExpander;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InterventionRequestTestCase extends TestCase
{
    protected InterventionRequest $interventionRequest;

    protected function setUp(): void
    {
        $cachePath = dirname(__DIR__).'/temp';
        $conf = new Configuration();
        $conf->setJpegoptimPath(null);
        $conf->setPngquantPath(null);
        $conf->setGcProbability(0);
        $conf->setTtl(0);
        $conf->setResponseTtl(0);
        $conf->setCachePath($cachePath);

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($cachePath)) {
            $fileSystem->mkdir($cachePath);
        }

        $conf->setUsePassThroughCache(false);
        $conf->setImagesPath(dirname(__DIR__).'/../public/images');
        $conf->setUseFileChecksum(false);
        $conf->setDriver('gd');
        $conf->setDefaultQuality(80);

        $fileResolver = new LocalFileResolver($conf->getImagesPath());

        $log = new Logger('InterventionRequest');
        $log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

        $this->interventionRequest = new InterventionRequest(
            $conf,
            $fileResolver,
            $log,
            new ImageEncoder()
        );
    }

    protected function tearDown(): void
    {
        $cachePath = dirname(__DIR__).'/temp';
        $fileSystem = new Filesystem();
        $pattern = glob($cachePath.'/*');
        if (false === $pattern) {
            return;
        }
        foreach ($pattern as $file) {
            $fileSystem->remove($file);
        }
    }

    protected function generateRequest(string $url): Request
    {
        $request = Request::create($url);

        $expander = new ShortUrlExpander($request);
        $expander->setIgnorePath('/assets');
        $params = $expander->parsePathInfo();
        if (null !== $params && is_string($params['queryString']) && is_string($params['filename'])) {
            $expander->injectParamsToRequest($params['queryString'], $params['filename']);
        }

        return $request;
    }

    protected function getResponseFileMd5(Response $response): string
    {
        return md5($response->getContent() ?: '');
    }
}
