<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\RequestEvent;
use AM\InterventionRequest\FileResolverInterface;
use AM\InterventionRequest\FlysystemFileResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
 * Direct stream no-process files when FileResolver is a FlysystemFileResolver.
 */
final class StreamNoProcessListener implements EventSubscriberInterface
{
    public const ATTRIBUTE = 'stream_no_process';

    public function __construct(
        private readonly FileResolverInterface $fileResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 1000],
        ];
    }

    public function onRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();
        $hasNoProcess = $request->query->get('no_process', false);

        if (!$hasNoProcess) {
            return;
        }
        if (!$this->fileResolver instanceof FlysystemFileResolver) {
            return;
        }

        $request->attributes->set('no_process', true);
        $request->attributes->set(self::ATTRIBUTE, true);

        $filesystem = $this->fileResolver->getFilesystem();
        $nativeImage = $this->fileResolver->resolveFile(
            $this->fileResolver->assertRequestedFilePath($request->get('image'))
        );

        $path = $nativeImage->getPathname();
        $size = $filesystem->fileSize($path);
        $start = 0;
        $end = $size - 1;
        $status = Response::HTTP_OK;
        $lastModified = new \DateTimeImmutable('@'.$filesystem->lastModified($path));

        // ponytail: single byte range only; multi-range requests get the whole file (allowed by RFC 9110)
        $range = $request->isMethod('GET') ? $request->headers->get('Range') : null;
        // RFC 9110 13.1.5: a stale If-Range validator means the client wants the whole file
        $ifRange = $request->headers->get('If-Range');
        if (null !== $ifRange && $ifRange !== $lastModified->format('D, d M Y H:i:s').' GMT') {
            $range = null;
        }
        if (
            null !== $range
            && 1 === preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $matches)
            && '' !== $matches[1].$matches[2]
            // RFC 9110 14.1.1: last-pos < first-pos is invalid, ignore the Range
            && ('' === $matches[1] || '' === $matches[2] || (int) $matches[2] >= (int) $matches[1])
        ) {
            if ('' === $matches[1]) {
                $start = max(0, $size - (int) $matches[2]);
            } else {
                $start = (int) $matches[1];
                if ('' !== $matches[2]) {
                    $end = min((int) $matches[2], $size - 1);
                }
            }
            if ($start > $end) {
                $requestEvent->setResponse(new Response(null, Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                    'Content-Range' => 'bytes */'.$size,
                ]));

                return;
            }
            $status = Response::HTTP_PARTIAL_CONTENT;
        }
        $length = $end - $start + 1;

        $response = new StreamedResponse(function () use ($path, $filesystem, $start, $length) {
            $outputStream = fopen('php://output', 'wb');
            if (false === $outputStream) {
                throw new \RuntimeException('Could not open php://output');
            }
            $inputStream = $filesystem->readStream($path);
            if ($start > 0 && stream_get_meta_data($inputStream)['seekable']) {
                fseek($inputStream, $start);
            } elseif ($start > 0) {
                // Non-seekable stream (remote storage): skip bytes by reading them
                $skip = $start;
                while ($skip > 0 && !feof($inputStream)) {
                    $chunk = fread($inputStream, min(8192, $skip));
                    if (false === $chunk || '' === $chunk) {
                        break;
                    }
                    $skip -= strlen($chunk);
                }
            }
            stream_copy_to_stream($inputStream, $outputStream, $length);
            fclose($inputStream);
        }, $status, [
            'Content-Type' => $filesystem->mimeType($path),
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'filename="'.$nativeImage->getFilename().'"',
            'X-Direct-Stream' => '1',
        ]);
        if (Response::HTTP_PARTIAL_CONTENT === $status) {
            $response->headers->set('Content-Range', sprintf('bytes %d-%d/%d', $start, $end, $size));
        }
        $response->setLastModified($lastModified);
        $response->setPublic();
        $requestEvent->setResponse($response);
    }
}
