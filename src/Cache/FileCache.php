<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Cache;

use AM\InterventionRequest\Encoder\ImageEncoder;
use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\FileResolverInterface;
use AM\InterventionRequest\Listener\StreamNoProcessListener;
use AM\InterventionRequest\NextGenFile;
use AM\InterventionRequest\Processor\ChainProcessor;
use AM\InterventionRequest\ShortUrlExpander;
use Intervention\Image\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileCache implements EventSubscriberInterface
{
    protected string $cachePath;
    private ImageEncoder $imageEncoder;

    public function __construct(
        protected readonly ChainProcessor $chainProcessor,
        protected readonly FileResolverInterface $fileResolver,
        string $cachePath,
        protected readonly LoggerInterface $logger,
        protected readonly int $ttl = 604800,
        protected readonly int $gcProbability = 300,
        protected readonly bool $useFileChecksum = false,
    ) {
        $cachePath = realpath($cachePath);
        if (false === $cachePath) {
            throw new \InvalidArgumentException('Cache path does not exist.');
        }
        $this->cachePath = $cachePath;
        $this->imageEncoder = new ImageEncoder($fileResolver);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', -100],
        ];
    }

    protected function saveImage(Image $image, string $cacheFilePath, int $quality): Image
    {
        return $this->imageEncoder->save($image, $cacheFilePath, $quality);
    }

    /**
     * Determines if the garbage collector should run for this request.
     */
    private function garbageCollectionShouldRun(Request $request): bool
    {
        if (true === (bool) $request->get('force_gc', false)) {
            return true;
        }

        if (mt_rand(1, $this->gcProbability) <= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks to see if the garbage collector should be initialized, and if it should, initializes it.
     */
    protected function initializeGarbageCollection(Request $request): void
    {
        if ($this->garbageCollectionShouldRun($request)) {
            $garbageCollector = new GarbageCollector($this->cachePath, $this->logger, $this->ttl);
            $garbageCollector->launch();
        }
    }

    protected function supports(RequestEvent $requestEvent): bool
    {
        $config = $requestEvent->getInterventionRequest()->getConfiguration();
        $streamNoProcess = $requestEvent->getRequest()->attributes->get(StreamNoProcessListener::ATTRIBUTE, false);

        return !$streamNoProcess && $config->hasCaching() && !$config->isUsingPassThroughCache();
    }

    /**
     * @throws \Exception
     */
    public function onRequest(RequestEvent $requestEvent, string $eventName, EventDispatcherInterface $dispatcher): void
    {
        if (!$this->supports($requestEvent)) {
            return;
        }
        $request = $requestEvent->getRequest();
        $sourceFilePath = $this->fileResolver->assertRequestedFilePath($request->get('image'));
        $nativeImage = $this->fileResolver->resolveFile($request->get('image'));
        $cacheFilePath = $this->getCacheFilePath($request, $nativeImage);
        $cacheFile = new File($cacheFilePath, false);
        $firstGen = false;

        $cacheFileExists = $this->fileResolver->cacheFileExists($cacheFilePath);

        /*
         * Check date, if cached date is lower than original date -> Remove cached file
         */
        if ($cacheFileExists) {
            $mtime_original_file = $this->fileResolver->getSourceLastModified($sourceFilePath);
            $mtime_cached_file = $this->fileResolver->getCacheLastModified($cacheFilePath);

            if ($mtime_cached_file < $mtime_original_file) {
                $this->fileResolver->deleteCacheFile($cacheFilePath);
            }
        }

        /*
         * First render cached image file.
         */
        if (!$cacheFileExists) {
            $image = $this->chainProcessor->process($nativeImage, $request);
            $this->saveImage($image, $cacheFilePath, $requestEvent->getQuality());
            // create the ImageSavedEvent and dispatch it to apply listeners
            // that require the image to be saved on disk.
            $dispatcher->dispatch(new ImageSavedEvent($image, $cacheFile, $requestEvent->getQuality()));
            $firstGen = true;
        }

        $fileContent = $this->fileResolver->getCacheStream($cacheFilePath);
        $response = new StreamedResponse(function () use ($fileContent) {
            $outputStream = fopen('php://output', 'wb');
            if (false === $outputStream) {
                throw new \RuntimeException('Could not open php://output');
            }
            stream_copy_to_stream(
                $fileContent,
                $outputStream
            );
        }, Response::HTTP_OK, [
            'Content-Type' => $this->fileResolver->getCacheMimeType($cacheFilePath),
            'Content-Disposition' => 'filename="'.$nativeImage->getRequestedFile()->getFilename().'"',
            'Last-Modified' => $this->fileResolver->getCacheLastModified($cacheFilePath),
            'X-IR-Cached' => '1',
            'X-IR-First-Gen' => (int) $firstGen,
        ]);
        $response->setPublic();

        $this->initializeGarbageCollection($request);
        $requestEvent->setResponse($response);
    }

    /**
     * @return string cache file relative path to cache folder
     */
    protected function getCacheFilePath(Request $request, File $nativeImage): string
    {
        /*
         * Get file MD5 to check real image integrity
         */
        if (true === $this->useFileChecksum) {
            $fileMd5 = hash_file('adler32', $nativeImage->getPathname());
        } else {
            $fileMd5 = $nativeImage->getPathname();
        }

        /*
         * Generate a unique cache hash key
         * which will be used as image path
         *
         * The key vary on request ALLOWED params and file md5
         * if enabled.
         */
        $cacheParams = [];
        foreach ($request->query->all() as $name => $value) {
            if (in_array($name, ShortUrlExpander::getAllowedOperationsNames())) {
                $cacheParams[$name] = $value;
            }
        }
        if ($nativeImage instanceof NextGenFile && $nativeImage->isNextGen()) {
            $cacheParams[$nativeImage->getNextGenExtension()] = true;
            $extension = $nativeImage->getNextGenExtension();
        } else {
            $cacheParams['webp'] = false;
            $cacheParams['avif'] = false;
            $cacheParams['heif'] = false;
            $cacheParams['heic'] = false;
            $extension = $nativeImage->getExtension();
        }
        $cacheHash = hash('sha1', serialize($cacheParams).$fileMd5);

        return substr($cacheHash, 0, 2).
            '/'.substr($cacheHash, 2, 2).
            '/'.substr($cacheHash, 4).'.'.$extension;
    }
}
