<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Vector;
use Intervention\Image\Geometry\Factories\EllipseFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class HotspotProcessor implements Processor
{
    use DimensionTrait;

    public function process(ImageInterface $image, Request $request): void
    {
        $crop = $this->validateDimensions($request, 'crop');
        $hotspot = $this->validateNormalizedHotspot($request, 'hotspot');

        if (null !== $hotspot && null !== $crop) {
            // If hotspot defines a new image area, we need to calculate the crop offset
            $rectInit = new Vector(
                $image->width() * $hotspot->topLeft->getX(),
                $image->height() * $hotspot->topLeft->getY(),
            );
            $rectSize = new Vector(
                ($image->width() * $hotspot->bottomRight->getX()) - $rectInit->getX(),
                ($image->height() * $hotspot->bottomRight->getY()) - $rectInit->getY(),
            );

            // Get width and height with ratio
            $croppedSize = $this->getCroppedWidthHeight($rectSize->getRoundedX(), $rectSize->getRoundedY(), $crop);
            $center = new Vector(
                $rectInit->getX() + $hotspot->center->getX() * $rectSize->getX(),
                $rectInit->getY() + $hotspot->center->getY() * $rectSize->getY()
            );
            $cropOffset = new Vector(
                max(
                    $rectInit->getX(),
                    min(
                        $rectInit->getX() + $rectSize->getX() - $croppedSize->getX(),
                        max(0, $center->getX() - ($croppedSize->getX() / 2))
                    )
                ),
                max(
                    $rectInit->getY(),
                    min(
                        $rectInit->getY() + $rectSize->getY() - $croppedSize->getY(),
                        max(0, $center->getY() - ($croppedSize->getY() / 2))
                    )
                )
            );

            // Debug mode draw crop and center point on image
            if ($request->attributes->get('trace', false)) {
                // Draw rectangle on initial crop
                $image->drawRectangle($rectInit->getRoundedX(), $rectInit->getRoundedY(), function (RectangleFactory $rectangle) use ($rectSize) {
                    $rectangle->width($rectSize->getRoundedX());
                    $rectangle->height($rectSize->getRoundedY());
                    $rectangle->background('#FF000040');
                });

                // Draw rectangle on final crop
                $image->drawRectangle($cropOffset->getRoundedX(), $cropOffset->getRoundedY(), function (RectangleFactory $rectangle) use ($croppedSize) {
                    $rectangle->width($croppedSize->getRoundedX() - 3);
                    $rectangle->height($croppedSize->getRoundedY() - 3);
                    $rectangle->border('#FF0000', 3);
                });

                // Draw green ellipse in center
                $image->drawEllipse($center->getRoundedX(), $center->getRoundedY(), function (EllipseFactory $ellipse) use ($croppedSize) {
                    $ellipse->background('#0FF000');
                    $ellipse->size((int) ($croppedSize->getX() / 35), (int) ($croppedSize->getX() / 35));
                });

                return;
            }

            $image->crop(
                $croppedSize->getRoundedX(),
                $croppedSize->getRoundedY(),
                $cropOffset->getRoundedX(),
                $cropOffset->getRoundedY(),
                background: '#000000',
            );
        }
    }
}
