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
 * @file WatermarkListener.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Listener;

use AM\InterventionRequest\Event\ImageAfterProcessEvent;
use AM\InterventionRequest\Event\ImageProcessEvent;
use AM\InterventionRequest\Event\ResponseEvent;
use Intervention\Image\AbstractFont;
use Intervention\Image\Image;

class WatermarkListener implements ImageEventSubscriberInterface
{
    private $watermarkText;
    private $size;
    private $color;
    private $align;
    private $valign;
    private $angle;
    /**
     * @var int
     */
    private $fontFile;

    /**
     * Add a watermark text on image center.
     *
     * @param string $watermarkText The text string that will be written to the image.
     * @param int|string $fontFile Set path to a True Type Font file or a integer value between 1 and 5 for one of the GD library internal fonts. Default: 1
     * @param int $size Set font size in pixels. Font sizing is only available if a font file is set and will be ignored otherwise. Default: 12
     * @param string|array $color Set color of the text in one of the available color formats. Default: #FFFFFF
     * @param string $align Set horizontal text alignment relative to given basepoint. Possible values are left, right and center. Default: center
     * @param string $valign Set vertical text alignment relative to given basepoint. Possible values are top, bottom and middle. Default: center
     * @param int $angle Set rotation angle of text in degrees. Text will be rotated counter-clockwise around the vertical and horizontal aligned point. Rotation is only available if a font file is set and will be ignored otherwise. Default: no rotation
     */
    public function __construct(
        $watermarkText,
        $fontFile = 1,
        $size = 24,
        $color = '#FFFFFF',
        $align = 'center',
        $valign = 'center',
        $angle = 0
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
    public static function getSubscribedEvents()
    {
        return [
            ImageAfterProcessEvent::class => 'watermarkImage',
            ResponseEvent::class => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event)
    {
        if ($this->supports($event->getImage())) {
            $response = $event->getResponse();
            $response->headers->set('X-IR-Watermarked', true);
            $event->setResponse($response);
        }
    }

    public function watermarkImage(ImageProcessEvent $event)
    {
        $image = $event->getImage();
        if ($this->supports($image)) {
            // use callback to define details
            $image->text(
                $this->watermarkText,
                $image->getWidth()/2,
                $image->getHeight()/2,
                function (AbstractFont $font) {
                    $font->file($this->fontFile);
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
     * @param Image $image
     * @return bool
     */
    public function supports(Image $image = null)
    {
        return null !== $image;
    }
}
