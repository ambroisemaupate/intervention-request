<?php
use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\InterventionRequest;
use AM\InterventionRequest\ShortUrlExpander;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

// include composer autoload
require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->usePutenv(true)->loadEnv(dirname(__DIR__) .  '/.env');

$request = Request::createFromGlobals();
$log = new Logger('InterventionRequest');
$log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

/*
 * A test configuration
 */
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
$conf->setDefaultQuality((string) getenv('IR_DEFAULT_QUALITY'));

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

/*
 * Handle main image request
 */
$iRequest = new InterventionRequest(
    $conf,
    $log
);

$iRequest->handleRequest($request);
$iRequest->getResponse($request)->send();
