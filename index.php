<?php
/**
 * Copyright © 2015, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file index.php
 * @author Ambroise Maupate
 */

// include composer autoload
require 'vendor/autoload.php';

// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
/*$cacheHash = md5(serialize($request->query->all()));
$cachePath = 'cache/' . implode('/', str_split($cacheHash, 8));
$cacheFile = new \SplFileInfo($cachePath);*/

// create an image manager instance with favored driver
$manager = new ImageManager([
    'driver' => 'gd',
    'cache' => [
        'path' => 'cache/'
    ]
]);

if (!$request->query->has('image') || !file_exists($request->query->get('image'))) {
    throw new \RuntimeException("No valid image file found", 1);
}

$image = $manager->cache(function($manager) use ($request) {
    // to finally create image instances
    return $manager->make($request->query->get('image'))->fit(960, 350);
}, 7*24*60*60, true);

/*$path = $cacheFile->getPath();
if (!file_exists($path)) {
    mkdir($path, 0777, true);
}
if (false === file_put_contents($cachePath, $image->response())) {
    throw new \RuntimeException("Impossible to write cache file (".$cachePath.")", 1);
}*/

// send HTTP header and output image data
echo $image->response();
