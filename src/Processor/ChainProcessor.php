<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ImageBeforeProcessEvent;
use AM\InterventionRequest\FileWithResourceInterface;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package AM\InterventionRequest\Processor
 */
final class ChainProcessor
{
    private Configuration $configuration;
    private EventDispatcherInterface $dispatcher;
    /**
     * @var Processor[]
     */
    private array $processors;

    /**
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

    protected function makeImage(File $nativeFile): Image
    {
        // create an image manager instance with favored driver
        $manager = new ImageManager([
            'driver' => $this->configuration->getDriver(),
        ]);

        if ($nativeFile instanceof FileWithResourceInterface && $nativeFile->getResource() !== null) {
            return $manager->make($nativeFile->getResource());
        }

        return $manager->make($nativeFile);
    }

    /**
     * @param File    $nativeImage
     * @param Request $request
     *
     * @return Image
     */
    public function process(File $nativeImage, Request $request): Image
    {
        if ($request->query->has('no_process')) {
            return $this->makeImage($nativeImage);
        }

        $beforeProcessEvent = new ImageBeforeProcessEvent($this->makeImage($nativeImage));
        $this->dispatcher->dispatch($beforeProcessEvent);

        /*
         * Get image altered by BEFORE subscribers
         */
        $image = $beforeProcessEvent->getImage();

        if (null === $image) {
            throw new \InvalidArgumentException('Image should not be null before process.');
        }

        foreach ($this->processors as $processor) {
            if ($processor instanceof Processor) {
                $processor->process($image, $request);
            }
        }

        $afterProcessEvent = new ImageAfterProcessEvent($image);
        $this->dispatcher->dispatch($afterProcessEvent);

        if (null === $image = $afterProcessEvent->getImage()) {
            throw new \InvalidArgumentException('Image should not be null after process.');
        }

        return $image;
    }
}
