<?php

declare(strict_types=1);

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Encoder\ImageEncoder;
use AM\InterventionRequest\FlysystemFileResolver;
use AM\InterventionRequest\InterventionRequest;
use AM\InterventionRequest\LocalFileResolver;
use AM\InterventionRequest\ShortUrlExpander;
use AsyncAws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\AsyncAwsS3\PortableVisibilityConverter;
use League\Flysystem\Filesystem;
use League\Flysystem\Visibility;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

// include composer autoload
require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->usePutenv(true)->loadEnv(dirname(__DIR__).'/.env');

$log = new Logger('InterventionRequest');
$log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

$conf = new Configuration();
$conf->setJpegoptimPath((string) getenv('IR_JPEGOPTIM_PATH'));
$conf->setPngquantPath((string) getenv('IR_PNGQUANT_PATH'));
$conf->setGcProbability((int) getenv('IR_GC_PROBABILITY'));
$conf->setTtl((int) getenv('IR_GC_TTL'));
$conf->setResponseTtl((int) getenv('IR_RESPONSE_TTL'));
$conf->setCachePath((string) getenv('IR_CACHE_PATH'));
$conf->setUsePassThroughCache((bool) getenv('IR_USE_PASSTHROUGH_CACHE'));
$conf->setImagesPath((string) getenv('IR_IMAGES_PATH'));
$conf->setUseFileChecksum((bool) getenv('IR_USE_FILECHECKSUM'));
$conf->setDriver((string) getenv('IR_DRIVER'));
$conf->setDefaultQuality((int) getenv('IR_DEFAULT_QUALITY'));
if (getenv('IR_WATERMARK_PATH')) {
    $conf->setWatermarkPath((string) getenv('IR_WATERMARK_PATH'));
}

if (
    false !== getenv('IR_AWS_ACCESS_KEY_ID')
    && false !== getenv('IR_AWS_ACCESS_KEY_SECRET')
) {
    $adapter = new AsyncAwsS3Adapter(
        new S3Client([
            'accessKeyId' => (string) getenv('IR_AWS_ACCESS_KEY_ID'),
            'accessKeySecret' => (string) getenv('IR_AWS_ACCESS_KEY_SECRET'),
            'endpoint' => (string) getenv('IR_AWS_ENDPOINT'),
            'region' => (string) getenv('IR_AWS_REGION'),
        ]),
        (string) getenv('IR_AWS_BUCKET'),
        (string) (getenv('IR_AWS_PATH_PREFIX') ?: ''),
        new PortableVisibilityConverter(
            Visibility::PRIVATE
        )
    );
    $fileResolver = new FlysystemFileResolver(
        new Filesystem($adapter),
        $log,
        $conf->getImagesPath()
    );
} else {
    $fileResolver = new LocalFileResolver($conf->getImagesPath());
}

/*
 * Handle main image request
 */
$app = new InterventionRequest(
    $conf,
    $fileResolver,
    $log,
    new ImageEncoder(),
    null,
    (bool) getenv('IR_DEBUG')
);

$handler = static function () use ($app) {
    $request = Request::createFromGlobals();
    /*
     * Handle short url with Url rewriting
     */
    $expander = new ShortUrlExpander($request);
    // Enables using /cache in request path to mimic a pass-through file serve.
    $expander->setIgnorePath((string) getenv('IR_IGNORE_PATH'));
    $params = $expander->parsePathInfo();
    if (null !== $params) {
        // this will convert rewritten path to request with query params
        $expander->injectParamsToRequest($params['queryString'], $params['filename']);
    }
    $app->handleRequest($request)->send(true);
};

$maxRequests = (int) ($_SERVER['MAX_REQUESTS'] ?? 0);
for ($nbRequests = 0; !$maxRequests || $nbRequests < $maxRequests; ++$nbRequests) {
    $keepRunning = \frankenphp_handle_request($handler);
    gc_collect_cycles();
    if (!$keepRunning) {
        break;
    }
}
