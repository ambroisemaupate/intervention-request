<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\AbstractFont;
use Intervention\Image\Image;

final readonly class WatermarkListener implements ImageEventSubscriberInterface
{
    /**
     * @param string       $watermarkText the text string that will be written to the image
     * @param int|string   $fontFile      Set path to a True Type Font file or a integer value between 1 and 5 for one of the GD library internal fonts. Default: 1
     * @param int          $size          Set font size in pixels. Font sizing is only available if a font file is set and will be ignored otherwise. Default: 12
     * @param string|array $color         Set color of the text in one of the available color formats. Default: #FFFFFF
     * @param string       $align         Set horizontal text alignment relative to given basepoint. Possible values are left, right and center. Default: center
     * @param string       $valign        Set vertical text alignment relative to given basepoint. Possible values are top, bottom and middle. Default: center
     * @param int          $angle         Set rotation angle of text in degrees. Text will be rotated counter-clockwise around the vertical and horizontal aligned point. Rotation is only available if a font file is set and will be ignored otherwise. Default: no rotation
     */
    public function __construct(
        private string $watermarkText,
        private int|string $fontFile = 1,
        private int $size = 24,
        private array|string $color = '#FFFFFF',
        private string $align = 'center',
        private string $valign = 'center',
        private int $angle = 0,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImageAfterProcessEvent::class => 'watermarkImage',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ((bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Watermarked', '1');
            $event->setResponse($response);
        }
    }

    public function watermarkImage(ImageAfterProcessEvent $event): void
    {
        $image = $event->getImage();
        if (null !== $image && $this->supports($image)) {
            // use callback to define details
            $image->text(
                $this->watermarkText,
                $image->getWidth() / 2,
                $image->getHeight() / 2,
                function (AbstractFont $font) {
                    $font->file((string) $this->fontFile);
                    $font->size($this->size);
                    $font->color($this->color);
                    $font->align($this->align);
                    $font->valign($this->valign);
                    $font->angle($this->angle);
                }
            );

            $event->setImage($image);
        }
    }

    public function supports(?Image $image = null): bool
    {
        return null !== $image;
    }
}
