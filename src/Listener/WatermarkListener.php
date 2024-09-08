<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\AbstractFont;
use Intervention\Image\Image;

/**
 * @package AM\InterventionRequest\Listener
 */
final class WatermarkListener implements ImageEventSubscriberInterface
{
    private string $watermarkText;
    private int $size;
    /**
     * @var array|string
     */
    private $color;
    private string $align;
    private string $valign;
    private int $angle;
    /**
     * @var int|string
     */
    private $fontFile;

    /**
     * @param string $watermarkText The text string that will be written to the image.
     * @param int|string $fontFile Set path to a True Type Font file or a integer value between 1 and 5 for one of the GD library internal fonts. Default: 1
     * @param int $size Set font size in pixels. Font sizing is only available if a font file is set and will be ignored otherwise. Default: 12
     * @param string|array $color Set color of the text in one of the available color formats. Default: #FFFFFF
     * @param string $align Set horizontal text alignment relative to given basepoint. Possible values are left, right and center. Default: center
     * @param string $valign Set vertical text alignment relative to given basepoint. Possible values are top, bottom and middle. Default: center
     * @param int $angle Set rotation angle of text in degrees. Text will be rotated counter-clockwise around the vertical and horizontal aligned point. Rotation is only available if a font file is set and will be ignored otherwise. Default: no rotation
     */
    public function __construct(
        string $watermarkText,
        $fontFile = 1,
        int $size = 24,
        $color = '#FFFFFF',
        string $align = 'center',
        string $valign = 'center',
        int $angle = 0
    ) {
        $this->watermarkText = $watermarkText;
        $this->size = $size;
        $this->color = $color;
        $this->align = $align;
        $this->valign = $valign;
        $this->angle = $angle;
        $this->fontFile = $fontFile;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ImageAfterProcessEvent::class => 'watermarkImage',
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
        if ((bool) $response->headers->get('X-IR-First-Gen')) {
            $response->headers->set('X-IR-Watermarked', '1');
            $event->setResponse($response);
        }
    }

    /**
     * @param ImageAfterProcessEvent $event
     * @return void
     */
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

    /**
     * @param Image|null $image
     * @return bool
     */
    public function supports(Image $image = null): bool
    {
        return null !== $image;
    }
}
