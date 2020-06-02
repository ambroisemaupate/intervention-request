<?php
/**
 * Copyright © 2017, Ambroise Maupate
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
 * @file JpegTranListener.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageSavedEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\Image;
use Symfony\Component\Process\Process;

/**
 * Class JpegTranListener
 *
 * @package AM\InterventionRequest\Listener
 */
class JpegTranListener implements ImageEventSubscriberInterface
{
    /**
     * @var string
     */
    protected $jpegtranPath;

    /**
     * JpegFileListener constructor.
     * @param string $jpegtranPath
     */
    public function __construct(string $jpegtranPath)
    {
        $this->jpegtranPath = $jpegtranPath;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ImageSavedEvent::class => 'onJpegImageSaved',
            ResponseEvent::class => 'onResponse',
        ];
    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->jpegtranPath !== '' &&
            $response->headers->get('Content-Type') === 'image/jpeg' &&
            (bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->add(['X-IR-JpegTran' => '1']);
            $event->setResponse($response);
        }
    }

    /**
     * @param Image $image
     * @return bool
     */
    public function supports(Image $image = null)
    {
        return null !== $image && $image->mime() === 'image/jpeg' && $this->jpegtranPath !== '';
    }

    /**
     * @param ImageSavedEvent $event
     * @return void
     */
    public function onJpegImageSaved(ImageSavedEvent $event)
    {
        if ($this->supports($event->getImage())) {
            $process = new Process([
                $this->jpegtranPath,
                '-copy', 'none',
                '-optimize',
                '-progressive',
                '-outfile', $event->getImageFile()->getPathname(),
                $event->getImageFile()->getPathname(),
            ]);
            $process->run();
        }
    }
}
