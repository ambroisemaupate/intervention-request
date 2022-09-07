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

namespace AM\InterventionRequest\Cache;

use AM\InterventionRequest\Event\RequestEvent;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * PassThroughFileCache acts as FileCache but uses the real request path
 * to be able to serve cache images without PHP.
 *
 * @package AM\InterventionRequest\Cache
 */
final class PassThroughFileCache extends FileCache
{
    /**
     * @param RequestEvent $requestEvent
     *
     * @return bool
     */
    protected function supports(RequestEvent $requestEvent): bool
    {
        $config = $requestEvent->getInterventionRequest()->getConfiguration();
        return $config->hasCaching() && $config->isUsingPassThroughCache();
    }

    /**
     * @param Request $request
     * @param File    $nativeImage
     *
     * @return string
     */
    protected function getCacheFilePath(Request $request, File $nativeImage): string
    {
        /*
         * Check that cache folder is really used in request
         */
        $documentRoot = $request->server->get('DOCUMENT_ROOT');
        if (!is_string($documentRoot)) {
            throw new \RuntimeException('DOCUMENT_ROOT server param is not set.');
        }
        $documentRoot = realpath($documentRoot);
        if (false === $documentRoot) {
            throw new \RuntimeException($request->server->get('DOCUMENT_ROOT') . ' path does not exist.');
        }
        $cacheFolder = str_replace($documentRoot, '', $this->cachePath);
        $cacheFolderRegex = '#^' . preg_quote($cacheFolder) . '#';
        if (0 === preg_match($cacheFolderRegex, $request->getPathInfo())) {
            if ($this->logger !== null) {
                $this->logger->error('Cache path was not found in your request path info.', [
                    'pathInfo' => $request->getPathInfo(),
                    'cacheRegex' => $cacheFolderRegex,
                ]);
            }
            throw new FileNotFoundException($request->getPathInfo());
        }

        return $documentRoot . $request->getPathInfo();
    }
}
