<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Cache;

use AM\InterventionRequest\Event\RequestEvent;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * PassThroughFileCache acts as FileCache but uses the real request path
 * to be able to serve cache images without PHP.
 */
final class PassThroughFileCache extends FileCache
{
    protected function supports(RequestEvent $requestEvent): bool
    {
        $config = $requestEvent->getInterventionRequest()->getConfiguration();

        return $config->hasCaching() && $config->isUsingPassThroughCache();
    }

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
            throw new \RuntimeException('DOCUMENT_ROOT path does not exist.');
        }
        $cacheFolder = str_replace($documentRoot, '', $this->cachePath);
        $cacheFolderRegex = '#^'.preg_quote($cacheFolder).'#';
        if (0 === preg_match($cacheFolderRegex, $request->getPathInfo())) {
            if (null !== $this->logger) {
                $this->logger->error('Cache path was not found in your request path info.', [
                    'pathInfo' => $request->getPathInfo(),
                    'cacheRegex' => $cacheFolderRegex,
                ]);
            }
            throw new FileNotFoundException($request->getPathInfo());
        }

        return $documentRoot.$request->getPathInfo();
    }
}
