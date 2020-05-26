<?php
declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ImageBeforeProcessEvent;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

final class ChainProcessor
{
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var Processor[]
     */
    private $processors;

    /**
     * ChainProcessor constructor.
     *
     * @param Configuration            $configuration
     * @param EventDispatcherInterface $dispatcher
     * @param Processor[]              $processors
     */
    public function __construct(Configuration $configuration, EventDispatcherInterface $dispatcher, array $processors)
    {
        $this->configuration = $configuration;
        $this->dispatcher = $dispatcher;
        $this->processors = $processors;
    }

    /**
     * @param File    $nativeImage
     * @param Request $request
     *
     * @return Image
     */
    public function process(File $nativeImage, Request $request): Image
    {
        // create an image manager instance with favored driver
        $manager = new ImageManager([
            'driver' => $this->configuration->getDriver(),
        ]);

        $beforeProcessEvent = new ImageBeforeProcessEvent($manager->make($nativeImage->getPathname()));
        $this->dispatcher->dispatch($beforeProcessEvent);

        /*
         * Get image altered by BEFORE subscribers
         */
        $image = $beforeProcessEvent->getImage();

        foreach ($this->processors as $processor) {
            if ($processor instanceof Processor) {
                $processor->process($image, $request);
            }
        }

        $afterProcessEvent = new ImageAfterProcessEvent($image);
        $this->dispatcher->dispatch($afterProcessEvent);

        /*
         * Get image altered by AFTER subscribers
         */
        return $afterProcessEvent->getImage();
    }
}
