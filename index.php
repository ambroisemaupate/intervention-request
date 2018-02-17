<?php
/**
 * Copyright (c) 2018  Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
$conf->setCachePath(APP_ROOT . '/cache');
//$conf->setUsePassThroughCache(true);
$conf->setImagesPath(APP_ROOT . '/test');
$conf->setUseFileChecksum(false);

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
