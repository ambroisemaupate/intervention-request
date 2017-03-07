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
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\ProcessBuilder;

class JpegTranListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $jpegtranPath;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * JpegFileListener constructor.
     * @param string $jpegtranPath
     * @param LoggerInterface $logger
     */
    public function __construct($jpegtranPath, LoggerInterface $logger = null)
    {
        $this->jpegtranPath = $jpegtranPath;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            ImageSavedEvent::NAME => 'onJpegImageSaved',
        );
    }

    /**
     * @param ImageSavedEvent $event
     */
    public function onJpegImageSaved(ImageSavedEvent $event)
    {
        if ($event->getImage()->mime() == "image/jpeg" &&
            $this->jpegtranPath != "") {
            $builder = new ProcessBuilder(array(
                $this->jpegtranPath,
                '-copy', 'none',
                '-optimize',
                '-progressive',
                '-outfile', $event->getImageFile()->getPathname(),
                $event->getImageFile()->getPathname(),
            ));
            $builder->getProcess()->run();

            if (null !== $this->logger) {
                $this->logger->info("Used jpegtran to minify file.", ['cmd' => $builder->getProcess()->getCommandLine()]);
            }
        }
    }
}