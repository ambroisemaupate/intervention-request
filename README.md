# Intervention Request

**A customizable *Intervention Image* wrapper to use image simple re-sampling features over urls and a configurable cache.**

[![Static analysis and code style](https://github.com/ambroisemaupate/intervention-request/actions/workflows/run-test.yml/badge.svg)](https://github.com/ambroisemaupate/intervention-request/actions/workflows/run-test.yml)
[![Packagist](https://img.shields.io/packagist/v/ambroisemaupate/intervention-request.svg)](https://packagist.org/packages/ambroisemaupate/intervention-request)
[![Packagist](https://img.shields.io/packagist/dt/ambroisemaupate/intervention-request.svg)](https://packagist.org/packages/ambroisemaupate/intervention-request)

- [Ready-to-go *Docker* image](#ready-to-go-docker-image)
    * [Use local file-system or a distant one](#use-local-file-system-or-a-distant-one)
    * [Docker Compose example](#docker-compose-example)
- [Install](#install)
- [Configuration](#configuration)
- [Available operations](#available-operations)
    * [Fit position](#fit-position)
- [Using standalone entry point](#using-standalone-entry-point)
- [Using as a library inside your projects](#using-as-a-library-inside-your-projects)
- [Use URL rewriting](#use-url-rewriting)
    * [Shortcuts](#shortcuts)
- [Use pass-through cache](#use-pass-through-cache)
- [Convert to webp](#convert-to-webp)
- [Force garbage collection](#force-garbage-collection)
    * [Using command-line](#using-command-line)
- [Extend Intervention Request](#extend-intervention-request)
    * [Add custom event subscribers](#add-custom-event-subscribers)
        + [Available events](#available-events)
        + [Listener examples](#listener-examples)
- [Performances](#performances)
- [Optimization](#optimization)
    * [jpegoptim](#jpegoptim)
    * [pngquant](#pngquant)
    * [oxipng](#oxipng)
    * [pingo](#pingo)
    * [kraken.io](#krakenio)
    * [tinyjpg.com](#tinyjpgcom)
    * [jpegtran](#jpegtran)
    * [Optimization benchmark](#optimization-benchmark)
- [Testing](#testing)
    * [Disable cache during development](#disable-cache-during-development)
    * [Test grid](#test-grid)
    * [Unit tests](#unit-tests)
        + [Test Strategy](#test-strategy)
        + [Running the Tests](#running-the-tests)
    * [Functional tests](#functional-tests)
        + [Test Strategy](#test-strategy-1)
        + [Running the Tests](#running-the-tests-1)
- [License](#license)

## Ready-to-go *Docker* image

Intervention Request is now available as a standalone Docker server to use with whatever CMS or language you need.
It declares two volumes: one for your images storage and one for cached files.

- `/app/public/images`: you can set it as read-only to prevent any write operation
- `/app/public/assets`: you must set it as read-write to allow cache files to be written

```php
docker pull ambroisemaupate/intervention-request;
# Make sure to share your volume with READ-ONLY flag
docker run -v "/my/images/folder:/app/public/images:ro" -p 8080:80/tcp ambroisemaupate/intervention-request;
```

- Create a `/my/images/folder/your-image.png`
- Then try http://localhost:8080/assets/f300x300-s5/images/your-image.png.webp

Garbage collector runs every hour as a `crontab` job and will purge cache files created more than `$IR_GC_TTL` seconds ago.
You still can execute it manually using `docker compose exec -u www-data intervention bin/intervention gc:launch` command.

### Use local file-system or a distant one

_InterventionRequest_ is built on [*Flysystem*](https://flysystem.thephpleague.com/docs/) library to abstract access to your native images.
Then you can store all your images on an _AWS_ bucket or a _Scaleway_ Object Storage and process them on the fly. **Processed images are still
cached on _InterventionRequest_ local storage.** This new file system abstraction layer allows multiple *InterventionRequest* docker
containers to run in parallel but still use the same storage as image backend, or simply detach your media storage logic from
_InterventionRequest_ application.

`InterventionRequest` object requires a `FileResolverInterface` which can be a `LocalFileResolver` or `FlysystemFileResolver`, then
`FlysystemFileResolver` must be provided a `League\Flysystem\Filesystem` object configured with any _Flysystem_ adapter.

If you prefer to use `ambroisemaupate/intervention-request` Docker image, environment variables are available for
_AWS_ adapter only.

### Docker Compose example

```yaml
version: '3'
services:
    intervention:
        image: ambroisemaupate/intervention-request:7.0.2
        #image: ambroisemaupate/intervention-request:frankenphp-7.0.2
        volumes:
            - cache:/app/public/assets
            ## If using local storage file system
            - ./my/images/folder:/app/public/images:ro
        # You can override some defaults below
        environment:
            IR_DEBUG: 0
            IR_DEFAULT_QUALITY: 80
            ## If using local storage file system
            IR_IMAGES_PATH: /app/public/images
            ## If using an AWS or Scaleway Object storage file system 
            IR_AWS_ACCESS_KEY_ID: 'changeme'
            IR_AWS_ACCESS_KEY_SECRET: 'changeme'
            IR_AWS_ENDPOINT: 'https://s3.fr-par.scw.cloud'
            IR_AWS_REGION: 'fr-par'
            IR_AWS_BUCKET: 'my-bucket'
            IR_AWS_PATH_PREFIX: 'images'
        ports:
            - 8080:80/tcp
        # Uncomment lines below for Traefik usage
        #labels:
        #    - "traefik.enable=true"
        #    - "traefik.http.services.intervention.loadbalancer.server.scheme=http"
        #    - "traefik.http.services.intervention.loadbalancer.server.port=80"
        #    - "traefik.http.services.intervention.loadbalancer.passhostheader=true"
        #    # Listen HTTP
        #    - "traefik.http.routers.intervention.entrypoints=http"
        #    - "traefik.http.routers.intervention.rule=Host(`intervention.test`)"
        #    - "traefik.http.routers.intervention.service=intervention"
        #networks:
        #    - default
        #    - frontproxynet
volumes:
    cache:
```

You don’t need to read further if you do not plan to embed this library in your PHP application.

## Install

```shell
composer require ambroisemaupate/intervention-request
```

Intervention Request is based on *symfony/http-foundation* component for handling
HTTP request, response and basic file operations. It wraps [*Intervention/image*](https://github.com/Intervention/image)
feature with a simple file cache managing.

## Configuration

Intervention request use a dedicated class to configure your image request
parameters. Before creating `InterventionRequest` object, you must instantiate
a new `AM\InterventionRequest\Configuration` object and set cache and images paths.

```php
$conf->setCachePath(APP_ROOT.'/cache');
$conf->setImagesPath(APP_ROOT.'/images');
```

This code will create a configuration with *cache* and *images* folders in the
same folder as your PHP script (`APP_ROOT`). **Notice that in the default `index.php` file, *images* path is defined to `/test` folder in order to use the testing images**. You should always set this path against your website images folder to prevent processing other files.

You can edit each configuration parameters using their corresponding *setters*:

- `setCaching(true|false)`: use or not request cache to store generated images on filesystem (default: `true`);
- `setCachePath(string)`: image cache folder path;
- `setUsePassThroughCache(true|false)`: use or not *pass-through* cache to by-pass PHP processing once image is generated;
- `setDefaultQuality(int)`: default 90, set the quality amount when user does not specify it;
- `setImagesPath(string)`: requested images root path;
- `setTtl(integer)`: cache images time to live for internal garbage collector (default: `1 week`);
- `setResponseTtl(integer)`: image HTTP responses time to live for browser and proxy caches (default: `1 year`);
- `setDriver('gd'|'imagick')`: choose an available *Image Intervention* driver;
- `setTimezone(string)`: PHP timezone to build \DateTime object used for caching. Set it here if you have not set it in your `php.ini` file;
- `setGcProbability(integer)`: Garbage collector probability divisor. Garbage collection launch probability is 1/$gcProbability where a probability of 1/1 will launch GC at every request.
- `setUseFileChecksum(true|false)`: Use file checksum to test if file is different even if its name does not change. This option can be greedy on large files. (default: `false`).
- `setJpegoptimPath(string)`: *Optional* — Tells where `jpegoptim` binary is for JPEG post-processing (not useful unless you need to stick to 100 quality).
- `setPngquantPath(string)`: *Optional* — Tells where `pngquant` binary is for PNG post-processing. **This post-processing tool is highly recommended** as PNG won’t be optimized without it.
- `setLossyPng(true|false)`: *Optional* — Tells `pngquant`/`pingo` binaries to use *palette* lossy compression (default: `false`).


## Available operations

| Query attribute     | Description                                                                                                                                                                                                                                                                                                                                                      | Usage                                 |
|---------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------|
| image               | Native image path relative to your configuration `imagePath`                                                                                                                                                                                                                                                                                                     | `?image=path/to/image.jpg`            |
| fit                 | [Crop and resize combined](https://image.intervention.io/v3/modifying-images/resizing#fitted-resizing-without-exceeding-the-original-size) It needs a `width` and a `height` in pixels, <br>this filter can be combined with `align` to choose which part of your image to fit                                                                                   | `…&fit=300x300`                       |
| align               | [Crop and resize combined](https://image.intervention.io/v3/modifying-images/resizing#fitted-resizing-without-exceeding-the-original-size) Choose which part of your image to fit.                                                                                                                                                                               | `…&align=c`                           |
| flip                | [Mirror image horizontal or vertical](https://image.intervention.io/v3/modifying-images/effects#mirror-image-horizontally) You can set `h` for horizontal or `v` for vertical flip.                                                                                                                                                                              | `…&flip=h`                            |
| crop                | [Crop an image](https://image.intervention.io/v3/modifying-images/resizing#crop-image) Crop and image by a **ratio** , using origin image size.                                                                                                                                                                                                                  | `…&crop=1:1`                          |
| width               | [Resize image proportionally to given width](https://image.intervention.io/v3/modifying-images/resizing#scale-images-but-do-not-exceed-the-original-size) It needs a `width` in pixels                                                                                                                                                                           | `…&width=300`                         |
| height              | [Resize image proportionally to given height](https://image.intervention.io/v3/modifying-images/resizing#scale-images-but-do-not-exceed-the-original-size) It needs a `height` in pixels                                                                                                                                                                         | `…&height=300`                        |
| crop + height/width | Do the same as *fit* using width or height as final size. Ex: this will output a 200 x 200px image                                                                                                                                                                                                                                                               | `…&crop=1:1&width=200`                |
| background          | [Matte a png file with a background color](https://image.intervention.io/v3/basics/colors#merge-transparent-areas-with-color)                                                                                                                                                                                                                                    | `…&background=ff0000`                 |
| greyscale/grayscale | [Turn an image into a greyscale version](https://image.intervention.io/v3/modifying-images/effects#convert-image-to-a-greyscale-version)                                                                                                                                                                                                                         | `…&greyscale=1`                       |
| blur                | [Blurs an image](https://image.intervention.io/v3/modifying-images/effects#blur-effect)                                                                                                                                                                                                                                                                          | `…&blur=20`                           |
| quality             | Set the exporting quality (1 - 100), default to 90                                                                                                                                                                                                                                                                                                               | `…&quality=95`                        |
| progressive         | [Toggle progressive mode](https://image.intervention.io/v3/basics/image-output#encode-images-by-media-mime-type)                                                                                                                                                                                                                                                 | `…&progressive=1`                     |
| interlace           | [Toggle interlaced mode](https://image.intervention.io/v3/basics/image-output#encode-images-by-media-mime-type)                                                                                                                                                                                                                                                  | `…&interlace=1`                       |
| sharpen             | [Sharpen image](https://image.intervention.io/v3/modifying-images/effects#sharpening-effect) (1 - 100)                                                                                                                                                                                                                                                           | `…&sharpen=10`                        |
| contrast            | [Change image contrast](https://image.intervention.io/v3/modifying-images/effects#change-the-image-contrast) (-100 to 100, 0 means no changes)                                                                                                                                                                                                                   | `…&contrast=10`                       |
| no_process          | **Disable all image processing by PHP**, this does not load image in memory but executes <br> any post-process optimizers (such as *pngquant*, *jpegoptim*…)                                                                                                                                                                                                     | `…&no_process=1`                      |
| hotspot             | [Crop an image](https://image.intervention.io/v3/modifying-images/resizing#crop-image) using a center point and a base area equal to original image. <br>It needs a `x`, `y` (to define the center point of the image in percentage between 0 and 1).  <br>[Since v6.0.0](https://github.com/ambroisemaupate/intervention-request/releases/tag/v6.0.0)           | `…&hotspot=0.25:0.75`                 |
| hotspot + area      | [Crop an image](https://image.intervention.io/v3/modifying-images/resizing#crop-image) with a base area and a center point (relative to the area).  <br>It needs `centerX`, `centerY`, `areaStartX`, `areaStartY`, `areaEndX`, `areaEndY` (all between 0 and 1). <br>[Since v7.0.0](https://github.com/ambroisemaupate/intervention-request/releases/tag/v7.0.0) | `…&hotspot=0.25:0.75:0.1:0.1:0.9:0.9` |

### Fit position

Due to URL rewriting, `align` filter can only takes one or two letters as a value. When no align filter is specified, `center` is used:

| URL value | Alignment    |
|-----------|--------------|
| `tl`      | top-left     |
| `t`       | top          |
| `tr`      | top-right    |
| `l`       | left         |
| `c`       | center       |
| `r`       | right        |
| `bl`      | bottom-left  |
| `b`       | bottom       |
| `br`      | bottom-right |

## Using standalone entry point

An `index.php` file enables you to use this tool as a standalone app. You can
adjust your configuration to set your native images folder or enable/disable cache.

Setup it on your webserver root, in a `intervention-request` folder
and call this url (for example using MAMP/LAMP on your computer with included test images):
`http://localhost:8888/intervention-request/?image=images/testPNG.png&fit=100x100`

## Using as a library inside your projects

`InterventionRequest` class works seamlessly with *Symfony* `Request` and `Response`. It’s
very easy to integrate it in your *Symfony* controller scheme:

```php
use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\InterventionRequest;
use AM\InterventionRequest\LocalFileResolver;
use Monolog\Logger;

/*
 * A test configuration
 */
$conf = new Configuration();
$conf->setCachePath(APP_ROOT.'/cache');
$conf->setImagesPath(APP_ROOT.'/files');
// Comment this line if jpegoptim is not available on your server
$conf->setJpegoptimPath('/usr/local/bin/jpegoptim');
// Comment this line if pngquant is not available on your server
$conf->setPngquantPath('/usr/local/bin/pngquant');

$fileResolver = new LocalFileResolver($conf->getImagesPath());

/*
 * InterventionRequest constructor asks 2 objects:
 *
 * - AM\InterventionRequest\Configuration
 * - AM\InterventionRequest\FileResolverInterface
 */
$intRequest = new InterventionRequest($conf, $fileResolver, new Logger('InterventionRequest'));
// Handle request and process image
$intRequest->handleRequest($request);

// getResponse returns a Symfony\Component\HttpFoundation\Response object
// with image mime-type and data. All you need is to send it!
return $intRequest->getResponse($request);
```

## Use URL rewriting

If you want to use clean URL. You can add `ShortUrlExpander` class to listen
to shorten URL like: `http://localhost:8888/intervention-request/f100x100-g/images/testPNG.png`.

First, add an `.htaccess` file (or its *Nginx* equivalent) to activate rewriting:

```apache
# .htaccess
# Pretty URLs
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [S=40]
RewriteRule . index.php [L]
</IfModule>
```

Then add these lines to your application before handling `InterventionRequest`.
`ShortUrlExpander` will work on your existing `$request` object.

```php
use AM\InterventionRequest\ShortUrlExpander;
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
```

### Shortcuts

URL shortcuts can be combined using `-` (dash) character.
For example `f100x100-q50-g1-p0` stands for `fit=100x100&quality=50&greyscale=1&progressive=0`.

| Query attribute                                                           | Shortcut letter |
|---------------------------------------------------------------------------|-----------------|
| align                                                                     | a               |
| fit                                                                       | f               |
| flip                                                                      | m               |
| crop                                                                      | c               |
| width                                                                     | w               |
| height                                                                    | h               |
| background                                                                | b               |
| greyscale                                                                 | g               |
| blur                                                                      | l               |
| quality                                                                   | q               |
| progressive                                                               | p               |
| interlace                                                                 | i               |
| sharpen                                                                   | s               |
| contrast *(only from 0 to 100)*                                           | k               |
| no_process *(do not process and load image in memory, allows optimizers)* | n               |
| hotspot                                                                   | d               |


## Use pass-through cache

Intervention request can save your images in a public folder to let *Apache* or *Nginx* serve them once they’ve been generated. This can reduce *time-to-first-byte* as PHP is not called any more.

- Make sure you have configured *Apache* or *Nginx* to serve real files **before** proxying your request to PHP. Otherwise this could lead to file overwriting!
- Pass-through cache is only available if you are using `ShortUrlExpander` to mimic a real image path without any query-string.
- Your cache folder **must** be public (in your document root), so your documents will be visible to anyone. If your images must be protected behind a PHP firewall, you should not activate *pass-through* cache.
- **Garbage collector won’t be called** because cached image won’t be served by your PHP server anymore but *Apache* or *Nginx*
- Pass-through cache will save image for the first time at the real path used in your request, make sure it won’t overwrite any application file.

Define your configuration cache path to a public folder:

```php
$conf = new Configuration();
$conf->setCachePath(APP_ROOT . '/cache');
$conf->setUsePassThroughCache(true);
```

Then enable the `ShortUrlExpander` and **ignore your cache path** to process only path info after it.
```php
$expander = new ShortUrlExpander($request);
// Enables using /cache in request path to mimic a pass-through file serve.
$expander->setIgnorePath('/cache');
```

## Convert to webp

**Make sure your PHP is compiled with WebP image format.**

Intervention Request can automatically generated webp images by appending `.webp` to an existing image file.

Use `/image.jpg.webp` for `/image.jpg` file.

Intervention Request will look for a image file without `.webp` extension and throw a 404 error if it does not exist.

## Force garbage collection

### Using command-line

```shell
bin/intervention gc:launch /path/to/my/cache/folder --log /path/to/my/log/file.log
```

## Extend Intervention Request

Intervention Request uses *Processors* to alter original images. By default, each
available operation is handled by one `AbstractProcessor` inheriting class (look at
the `src/Processor` folder).

You can create your own *Processors* and override default ones by injecting an array
to your `InterventionRequest` object.

```php
/*
 * Handle main image request with a
 * custom list of Processors.
 */
$iRequest = new InterventionRequest(
    $conf,
    $fileResolver,
    $log,
    [
        new Processor\WidenProcessor(),
        // add or replace with your own Processors
    ],
    $debug
);
```

Be careful, *Processors* position in this array is very important, please look at
the default one in `InterventionRequest.php` class. Resizing processors should be
the first, and quality processors should be the last as image operations will be done
following your processors ordering.

### Add custom event subscribers

You can create custom actions if you need to optimize/alter your images before they get served
using `ImageSavedEvent` and *Symfony* event system :

Create a class implementing `ImageEventSubscriberInterface` and, for example, listen to `ImageSavedEvent::NAME`

```php
public static function getSubscribedEvents()
{
    return [
        ImageSavedEvent::class => 'onImageSaved',
    ];
}
```

This event will carry a `ImageSavedEvent` object with all you need to optimize/alter it.
Then, use `$interventionRequest->addSubscriber($yourSubscriber)` method to register it.

#### Available events

| Event name                       | Description                                                                                                                      |
|----------------------------------|----------------------------------------------------------------------------------------------------------------------------------|
| `RequestEvent::class`            | Main request handling event which handles `quality` and image processing and caching.                                            |
| `ImageBeforeProcessEvent::class` | Before `Image` is being processed.                                                                                               |
| `ImageAfterProcessEvent::class`  | After `Image` has been processed.                                                                                                |
| `ImageSavedEvent::class`         | After `Image` has been saved to filesystem with a physical file-path. **This event is only dispatched if *caching* is enabled.** |
| `ResponseEvent::class`           | After Symfony’s response has been built with image data. (Useful to alter headers)                                               |

#### Listener examples

- `WatermarkListener` will print text on your image
- `KrakenListener` will optimize your image file using *kraken.io* external service
- `TinifyListener` will optimize your image file using *tinyjpg.com* external service
- `JpegTranListener` will optimize your image file using local `jpegtran` binary

Of course, you can build your own listeners and share them with us!

## Performances

If your *Intervention-request* throws errors like that one:

```
Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 5184 bytes).
```

It’s because you are trying to process a too large image. The solution is too increase your `memory_limit`
PHP setting over `256M`. You can edit this file in your server `php.ini` file.

You can use `ini_set('memory_limit', '256M');` in your `index.php` file if your hosting plan
allows you to dynamically change PHP configuration.

In general, we encourage to always downscale your native images before using them with
*Intervention-request*. Raw jpeg images coming from your DSLR camera will give your
PHP server a very hard time to process.

## Optimization

### jpegoptim

If you have `jpegoptim` installed on your server, you can add it to your configuration

```php
$conf->setJpegoptimPath('/usr/local/bin/jpegoptim');
```

### pngquant

If you have `pngquant` installed on your server, you can add it to your configuration

```php
$conf->setPngquantPath('/usr/local/bin/pngquant');
$conf->setLossyPng(true); // use palette lossy png compression - default: false
```

### oxipng

If you have [`oxipng`](https://github.com/shssoichiro/oxipng) installed on your server, you can add it to your configuration

```php
$conf->setOxipngPath('/usr/local/bin/oxipng');
```

### pingo

If you have [`pingo`](https://css-ig.net/pingo) installed on your server and `Wine`, you can add it to your configuration

```php
$conf->setPingoPath('/usr/local/bin/pingo.exe');
$conf->setLossyPng(true); // use palette lossy png compression - default: false
$conf->setNoAlphaPingo(true); // Remove png transparency to compress more - default: false
```

### kraken.io

If you have subscribed to a paid [kraken.io](https://kraken.io) plan, you can add the dedicated
`KrakenListener` to send your resized images over the external service.

```php
$iRequest->addSubscriber(new \AM\InterventionRequest\Listener\KrakenListener(
    'your-api-key', 
    'your-api-secret', 
    true,
    $log
));
```

Pay attention, that images will be sent over *kraken.io* API, it will take some additional time.

### tinyjpg.com

If you have subscribed to a paid [tinyjpg.com](https://tinyjpg.com) plan, you can add the dedicated
`TinifyListener` to send your resized images over the external service.

```php
$iRequest->addSubscriber(new \AM\InterventionRequest\Listener\TinifyListener(
    'your-api-key',
    $log
));
```

Pay attention, that images will be sent over *kraken.io* API, it will take some additional time.

### jpegtran

If you want to use your system `jpegtran` or the *Mozjpeg* one, you can use the `JpegTranListener`.

```php
$iRequest->addSubscriber(new \AM\InterventionRequest\Listener\JpegTranListener(
    '/usr/local/opt/mozjpeg/bin/jpegtran',
    $log
));
```

### Optimization benchmark

With default quality to 90%. \
AVIF conversion only supports custom compiled *ImageMagick* and only support lossless encoding.

| Url                                 | PHP raw | *tinyjpg.com* | *Kraken.io* + lossy | jpegoptim | mozjpeg (jpegtran) | WebP (90%) | WebP (85%) | AVIF (100%) |
|-------------------------------------|---------|---------------|---------------------|-----------|--------------------|------------|------------|-------------|
| /test/images/testUHD.jpg?width=2300 | 405 kB  | 168 kB        | 187 kB              | 395 kB    | 390 kB             | 235 kB     | 155 kB     | 94 kB       |
| /test/images/testUHD.jpg?width=1920 | 294 kB  | 132 kB        | 134 kB              | 285 kB    | 282 kB             | 176 kB     | 115 kB     | 71 kB       |
| /test/images/rhino.jpg?width=1920   | 642 kB  | 278 kB        | 534 kB              | 598 kB    | 596 kB             | 564 kB     | 429 kB     | 398 kB      |
| /test/images/rhino.jpg?width=1280   | 325 kB  | 203 kB        | 278 kB              | 303 kB    | 301 kB             | 295 kB     | 229 kB     | 227 kB      |


| Url                      | PHP raw | pngquant | oxipng | *Kraken.io* + lossy | WebP (100%) | WebP (85%) | AVIF (100%) |
|--------------------------|---------|----------|--------|---------------------|-------------|------------|-------------|
| /test/images/testPNG.png | 292 kB  | 167 kB   | 288 kB | 142 kB              | 186 kB      | 28 kB      | 11.7 kB     |

## Testing

This project uses Docker for development environment.
Copy `compose.override.yml` to `compose.override.yml`, use `php-dev` target and declare a volume on your project root folder.

```yaml
services:
    intervention:
        build:
            context: .
            target: php-dev
        volumes:
            - ./:/app
        ports:
            -   "8080:80/tcp"

```

```bash
docker compose build
docker compose up
```

Then open `http://0.0.0.0:8080/assets/w300/rhino.jpg` in your browser.
You should be able to test *intervention-request* with Passthrough cache and *ShortUrl* enabled.
Set `IR_USE_PASSTHROUGH_CACHE=0` if you don't want cache to be served by *Nginx*.

### Disable cache during development    

Set these variables to avoid caching images during development:

```dotenv
IR_DEBUG: 1
IR_USE_PASSTHROUGH_CACHE: 0
IR_GC_PROBABILITY: 1
IR_GC_TTL: 0
```

### Test grid

A simple test grid is available at `http://localhost:8080/test.html`

### Unit tests

Make sure PHPUnit is properly configured and all required image processing dependencies are installed (such as GD, Imagick, or any relevant libraries your application uses).

This project includes a comprehensive set of unit tests to validate various image manipulation operations.
These tests ensure that image transformations are applied correctly and consistently.

Available Tests :

- testFitImageGeneration: Verifies that the image is resized to fit within the given dimensions while maintaining its aspect ratio.
- testCropImageGeneration: Validates cropping of an image to the specified size.
- testCropResizedImageGeneration: Validates cropping of an image with a specific ratio and alignment to the specified size.
- testBackgroundImageGeneration: Tests the application or modification of a background color or image.
- testBlurImageGeneration: Checks the application of a blur effect to the image.
- testContrastImageGeneration: Validates the adjustment of image contrast.
- testFlipImageGeneration: Verifies image flipping (horizontal and vertical).
- testHotspotImageGeneration: Ensures correct placement and rendering of a focal point ("hotspot") on the image.
- testRotateImageGeneration: Checks that the image is rotated by the correct angle.
- testSharpenImageGeneration: Verifies the application of a sharpening filter.
- testWidenImageGeneration: Tests image resizing that increases the width while preserving content.
- testHeightenImageGeneration: Tests image resizing that increases the height.

#### Test Strategy
Each image processing test validates the output by comparing the MD5 hash of the generated image with a known, expected hash.
This ensures that the image transformation produces exact and reproducible results, pixel for pixel.

For each test, the following approach is used:

- Load a source image (e.g., rhino.webp).
- Apply a specific transformation using query parameters (e.g., fit=500x500).
- Use a dedicated processor (e.g., FitProcessor) to apply the transformation.
- Save the processed image temporarily in a local output directory.
- Compute the MD5 hash of the generated image file.
- Compare it with the expected hash using an assertion.

This method ensures that even minor unintended changes in image generation logic will be detected during test runs.

#### Running the Tests

To run only the unit tests:

```bash
php -d "memory_limit=-1" vendor/bin/phpunit tests/Processor
```
OR
```bash
make test-unit
```

### Functional tests

This project includes a comprehensive set of unit tests to validate various image manipulation operations.
These tests ensure that image transformations are applied correctly and consistently.
Available Tests :

- **Fit Image Generation**: Validates that an image is resized proportionally to fit within specified dimensions.
- **Crop Image Generation**: Ensures correct cropping of an image to the desired size.
- **Crop and Resize Image Generation**: Checks cropping combined with specific ratio and alignment for accurate resizing.
- **Background Image Generation**: Tests the application or customization of a background (either color or another image).
- **Blur Effect Application**: Verifies proper application of a blur effect to an image.
- **Contrast Adjustment**: Validates that image contrast adjustments are rendered accurately.
- **Flip Operation**: Tests horizontal and vertical flipping of an image.
- **Hotspot Placement**: Ensures precise placement of a focal point or "hotspot" on the image.
- **Rotation**: Validates rotation transformations applied to an image.
- **Sharpening Filter**: Ensures consistent application of sharpen filters.
- **Widen Image Functionality**: Tests width increase transformations while maintaining image content.
- **Heighten Image Functionality**: Tests height increase transformations while retaining image quality and content.

#### Test Strategy

Each image manipulation strategy is tested via HTTP-like requests using the `InterventionRequest` handler.
This simulates real-world image transformations applied via URL parameters.

#### Running the Tests

To run only the functional tests:

```bash
php -d "memory_limit=-1" vendor/bin/phpunit tests/Functional
```
OR
```bash
make test-functional
```


## License

*Intervention Request* is handcrafted by *Ambroise Maupate* under **MIT license**.

Have fun!
