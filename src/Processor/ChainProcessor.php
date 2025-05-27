<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Configuration;
use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ImageBeforeProcessEvent;
use AM\InterventionRequest\FileWithResourceInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

final readonly class ChainProcessor
{
    /**
     * @param Processor[] $processors
     */
    public function __construct(
        private Configuration $configuration,
        private EventDispatcherInterface $dispatcher,
        private array $processors,
    ) {
    }

    protected function makeImage(File $nativeFile): ImageInterface
    {
        // create an image manager instance with favored driver
        $manager = new ImageManager($this->configuration->getDriver());

        if ($nativeFile instanceof FileWithResourceInterface && null !== $nativeFile->getResource()) {
            return $manager->read($nativeFile->getResource());
        }

        return $manager->read($nativeFile);
    }

    public function process(File $nativeImage, Request $request): ImageInterface
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
