<?php

define('APP_ROOT', dirname(__FILE__));

// include composer autoload
require 'vendor/autoload.php';

// import the Intervention Image Manager Class
use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\InterventionRequest;
use AM\InterventionRequest\ShortUrlExpander;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$log = new Logger('InterventionRequest');
$log->pushHandler(new StreamHandler('interventionRequest.log', Logger::INFO));

/*
 * A test configuration
 */
$conf = new Configuration();
//$conf->setJpegoptimPath('/usr/bin/jpegoptim');
//$conf->setPngquantPath('/usr/bin/pngquant');
//$conf->setGcProbability(1);
//$conf->setTtl(10);
//$conf->setResponseTtl(300);
$conf->setCachePath(APP_ROOT . '/cache');
//$conf->setUsePassThroughCache(true);
$conf->setImagesPath(APP_ROOT . '/test');
$conf->setUseFileChecksum(false);
//$conf->setDriver('imagick');

/*
 * Handle short url with Url rewriting
 */
$expander = new ShortUrlExpander($request);
// Enables using /cache in request path to mimic a pass-through file serve.
//$expander->setIgnorePath('/cache');
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
