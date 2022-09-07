<?php

/**
 * Copyright © 2016, Ambroise Maupate
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
 * @file PngFileListener.php
 * @author Ambroise Maupate
 */

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

final class PngquantListener implements ImageFileEventSubscriberInterface
{
    protected string $pngquantPath;
    protected bool $lossy = false;

    /**
     * @param string $pngquantPath
     * @param bool $lossy
     */
    public function __construct(string $pngquantPath, bool $lossy = false)
    {
        $this->pngquantPath = $pngquantPath;
        $this->lossy = $lossy;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ImageSavedEvent::class => 'onPngImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (
            $this->pngquantPath !== '' &&
            $response->headers->get('Content-Type') === 'image/png' &&
            (bool) $response->headers->get('X-IR-First-Gen')
        ) {
            $response->headers->add(['X-IR-Pngquant' => '1']);
            $response->headers->add(['X-IR-Pngquant-Lossy' => (int) $this->lossy]);
            $event->setResponse($response);
        }
    }

    /**
     * @param File|null $image
     * @return bool
     */
    public function supports(File $image = null): bool
    {
        return $this->pngquantPath !== '' && null !== $image && $image->getMimeType() === 'image/png';
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onPngImageSaved(ImageSavedEvent $event): void
    {
        if ($this->supports($event->getImageFile())) {
            $maxQuality = $event->getQuality();
            $minQuality = $maxQuality - 10;
            if ($maxQuality > 100) {
                $maxQuality = 100;
            }
            if ($minQuality < 0) {
                $minQuality = 0;
            }
            $process = new Process([
                $this->pngquantPath,
                '-f',
                '--speed',
                '2',
                $this->lossy ? '--quality=' . sprintf('%d-%d', $minQuality, $maxQuality) : '',
                '-o',
                $event->getImageFile()->getPathname(),
                $event->getImageFile()->getPathname(),
            ]);
            $process->run();
        }
    }
}
