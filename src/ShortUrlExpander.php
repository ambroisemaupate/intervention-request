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
 * @file ShortUrlExpander.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest;

use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class ShortUrlExpander
{
    protected $request;

    protected static $operations = array(
        'c' => 'crop',
        'w' => 'width',
        'h' => 'height',
        'g' => 'greyscale',
        'l' => 'blur',
        'q' => 'quality',
        'f' => 'fit',
        'r' => 'rotate',
        'b' => 'background',
        'i' => 'interlace',
        'p' => 'progressive',
        's' => 'sharpen',
        'k' => 'contrast',
    );

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Parse query stirg and filename from request pathinfo.
     *
     * @return array|null
     */
    public function parsePathInfo()
    {
        if (preg_match('#(?P<queryString>[a-zA-Z:0-9\\-]+)/(?P<filename>[a-zA-Z0-9\\-_\\./]+)$#s', $this->request->getPathInfo(), $matches)) {
            return $matches;
        } else {
            return null;
        }
    }

    /**
     * Convert param shortcuts to full request GET params.
     *
     * @param  string $queryString
     * @param  string $filename
     */
    public function injectParamsToRequest($queryString, $filename)
    {
        $this->request->query->set('image', $filename);
        $params = explode('-', $queryString);

        foreach ($params as $param) {
            preg_match("/(?P<operation>[a-z]{1})(?P<value>[\S]*)/", $param, $matches);
            if (isset($matches['operation']) &&
                isset(static::$operations[$matches['operation']])) {
                $this->request->query->set(
                    static::$operations[$matches['operation']],
                    $matches['value']
                );
            }
        }
    }
}
