# Intervention Request

**A *Intervention Image* wrapper to use simple resample features over urls.**

## Install

```shell
composer install --no-dev -o
```

Intervention Request is based on *symfony/http-foundation* component for handling
HTTP request, response and basic file operations. It wraps [*Intervention/image*](https://github.com/Intervention/image)
feature with a simple file cache managing.

## Available operations

|  Query attribute  |  Description  |  Usage  |
| ----------------- | ------------- | ------------- |
| image | Native image path relative to your configuration `imagePath` | `?image=path/to/image.jpg` |
| fit | [Crop and resize combined](http://image.intervention.io/api/fit) It needs a `width` and a `height` in pixels | `…&fit=300x300` |
| crop | [Crop an image](http://image.intervention.io/api/crop) It needs a `width` and a `height` in pixels | `…&crop=300x300` |
| width | [Resize image proportionally to given width](http://image.intervention.io/api/widen) It needs a `width` in pixels | `…&width=300` |
| height | [Resize image proportionally to given height](http://image.intervention.io/api/heighten) It needs a `height` in pixels | `…&height=300` |
| crop + height/width | Do the same as *fit* using width or height as final size | `…&crop=300x300&width=200`: This will output a 200 x 200px image |
| greyscale/grayscale | [Turn an image into a greyscale version](http://image.intervention.io/api/greyscale) | `…&greyscale=1` |
| quality | Set the exporting quality (1 - 100), default to 90 | `…&quality=95` |

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
to shorten URL like: `http://localhost:8888/intervention-request/f100x100/images/testPNG.png`.

First, add a .htaccess file to activate rewriting.

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
`ShortUrlExpander` will work on your exiisting `$request` object.

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

## Force garbage collection

### Using command-line

```shell
bin/intervention gc:launch /path/to/my/cache/folder --log /path/to/my/log/file.log
```

## License

*Intervention Request* is handcrafted by *Ambroise Maupate* under **MIT license**.

Have fun!
