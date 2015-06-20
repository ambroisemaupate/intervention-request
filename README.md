# Intervention Request

**A *Intervention Image* wrapper to use simple resample features over urls.**

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2a4900b9-ca14-4740-b688-116602b16440/mini.png)](https://insight.sensiolabs.com/projects/2a4900b9-ca14-4740-b688-116602b16440)

## Install

```shell
composer install --no-dev -o
```

Intervention Request is based on *symfony/http-foundation* component for handling
HTTP request, response and basic file operations. It wraps [*Intervention/image*](https://github.com/Intervention/image)
feature with a simple file cache managing.

## Configuration

Intervention request use a dedicated class to configure your image request
parameters. Before creating `InterventionRequest` object, you must instanciate
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
- `setImagesPath(string)`: requested images root path; 
- `setTtl(integer)`: cache images time to live;
- `setDriver('gd'|'imagick')`: choose an available *Image Intervention* driver;
- `setTimezone(string)`: PHP timezone to build \DateTime object used for caching. Set it here if you have not set it in your `php.ini` file;
- `setGcProbability(integer)`: Garbage collector probability divisor. Garbage collection launch probability is 1/$gcProbability where a probability of 1/1 will launch GC at every request.


## Available operations

|  Query attribute  |  Description  |  Usage  |
| ----------------- | ------------- | ------------- |
| image | Native image path relative to your configuration `imagePath` | `?image=path/to/image.jpg` |
| fit | [Crop and resize combined](http://image.intervention.io/api/fit) It needs a `width` and a `height` in pixels | `…&fit=300x300` |
| crop | [Crop an image](http://image.intervention.io/api/crop) It needs a `width` and a `height` in pixels | `…&crop=300x300` |
| width | [Resize image proportionally to given width](http://image.intervention.io/api/widen) It needs a `width` in pixels | `…&width=300` |
| height | [Resize image proportionally to given height](http://image.intervention.io/api/heighten) It needs a `height` in pixels | `…&height=300` |
| crop + height/width | Do the same as *fit* using width or height as final size | `…&crop=300x300&width=200`: This will output a 200 x 200px image |
| background | [Matte a png file with a background color](http://image.intervention.io/api/limitColors) | `…&background=ff0000` |
| greyscale/grayscale | [Turn an image into a greyscale version](http://image.intervention.io/api/greyscale) | `…&greyscale=1` |
| blur | [Blurs an image](http://image.intervention.io/api/blur) | `…&blur=20` |
| quality | Set the exporting quality (1 - 100), default to 90 | `…&quality=95` |
| progressive | [Toggle progressive mode](http://image.intervention.io/api/interlace) | `…&progressive=1` |
| interlace | [Toggle interlaced mode](http://image.intervention.io/api/interlace) | `…&interlace=1` |
| sharpen | [Sharpen image](http://image.intervention.io/api/sharpen) (1 - 100) | `…&sharpen=10` |
| contrast | [Change image contrast](http://image.intervention.io/api/contrast) (-100 to 100, 0 means no changes) | `…&contrast=10` |

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

/*
 * A test configuration
 */
$conf = new Configuration();
$conf->setCachePath(APP_ROOT.'/cache');
$conf->setImagesPath(APP_ROOT.'/files');

/*
 * InterventionRequest constructor asks 2 objects:
 *
 * - AM\InterventionRequest\Configuration
 * - Symfony\Component\HttpFoundation\Request
 */
$intRequest = new InterventionRequest($conf, $request);
// Handle request and process image
$intRequest->handle();

// getResponse returns a Symfony\Component\HttpFoundation\Response object
// with image mime-type and data. All you need is to send it!
return $intRequest->getResponse();
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
$params = $expander->parsePathInfo();
if (null !== $params) {
    // this will convert rewritten path to request with query params
    $expander->injectParamsToRequest($params['queryString'], $params['filename']);
}
```

### Shortcuts

URL shortcuts can be combined using `-` (dash) character.
For example `f100x100-q50-g1-p0` stands for `fit=100x100&quality=50&greyscale=1&progressive=0`.

|  Query attribute  |  Shortcut letter  |
| ----------------- | ------------- |
| fit | f |
| crop | c |
| width | w |
| height | h |
| background | b |
| greyscale | g |
| blur | l |
| quality | q |
| progressive | p |
| interlace | i |
| sharpen | s |
| contrast *(only from 0 to 100)* | k |

## Force garbage collection

### Using command-line

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
    $request,
    $log,
    [
        new Processor\WidenProcessor($request),
        // add or replace with your own Processors
    ]
);
```

Be careful, *Processors* position in this array is very important, please look at
the default one in `InterventionRequest.php` class. Resizing processors should be
the first, and quality processors should be the last as image operations will be done
following your processors ordering.


## License

*Intervention Request* is handcrafted by *Ambroise Maupate* under **MIT license**.

Have fun!
