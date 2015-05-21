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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

$request = Request::createFromGlobals();

if (!$request->query->has('image') || !file_exists($request->query->get('image'))) {
    throw new \RuntimeException("No valid image file found", 1);
}

$realImage = new File($request->query->get('image'));

$cacheHash = hash('crc32b', serialize($request->query->all()));
$cachePath = 'cache/' . implode('/', str_split($cacheHash, 2)) . '.' . $realImage->getExtension();


try {
    $cacheFile = new File($cachePath);
    $response = new Response(
        file_get_contents($cachePath),
        Response::HTTP_OK,
        [
            'Content-Type' => $cacheFile->getMimeType(),
            'Content-Disposition' => 'filename="'.$realImage->getFilename().'"',
            'X-Generator-Cached' => true,
        ]
    );

} catch (FileNotFoundException $e) {

    // create an image manager instance with favored driver
    $manager = new ImageManager([
        'driver' => 'gd',
        'cache' => [
            'path' => 'cache/'
        ]
    ]);

    $image = $manager->make($request->query->get('image'));

    if ($request->query->has('crop') &&
        1 === preg_match('#^([0-9]+)[x\:]([0-9]+)$#', $request->query->get('crop'), $crop)) {
        $image->crop($crop[1], $crop[2], function ($constraint) {
            $constraint->upsize();
        });
    }
    if ($request->query->has('fit') &&
        1 === preg_match('#^([0-9]+)[x\:]([0-9]+)$#', $request->query->get('fit'), $fit)) {
        $image->fit($fit[1], $fit[2], function ($constraint) {
            $constraint->upsize();
        });
    }
    if ($request->query->has('width') &&
        1 === preg_match('#^([0-9]+)$#', $request->query->get('width'), $width)) {
        $image->widen($width[1], function ($constraint) {
            $constraint->upsize();
        });
    }
    if ($request->query->has('height') &&
        1 === preg_match('#^([0-9]+)$#', $request->query->get('height'), $height)) {
        $image->heighten($height[1], function ($constraint) {
            $constraint->upsize();
        });
    }
    if ($request->query->has('quality') &&
        1 === preg_match('#^([0-9]+)$#', $request->query->get('quality'), $quality)) {

        $quality[1] = (int) $quality[1];

        if ($quality[1] <= 100 &&
            $quality[1] > 0) {
            $quality = $quality[1];
        } else {
            $quality = 90;
        }
    } else {
        $quality = 90;
    }

    if ($request->query->has('greyscale')) {
        $image->greyscale();
    }


    $path = dirname($cachePath);
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    $image->save($cachePath, $quality);

    // send HTTP header and output image data
    $response = new Response(
        (string) $image->encode(null, $quality),
        Response::HTTP_OK,
        [
            'Content-Type' => $image->mime(),
            'Content-Disposition' => 'filename="'.$realImage->getFilename().'"',
            'X-Generator-First-Render' => true,
        ]
    );
    $response->setLastModified(new \DateTime('now'));
}

$response->setMaxAge(7*24*60*60);
$response->setPublic(true);
$response->setCharset('UTF-8');
$response->prepare($request);
$response->send();



