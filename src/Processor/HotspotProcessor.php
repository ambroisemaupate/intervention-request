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

    public function __construct(
        private ?bool $debug = false,
    ) {
    }

    public function process(ImageInterface $image, Request $request): void
    {
        $crop = $this->validateDimensions($request, 'crop');
        $hotspot = $this->validateNormalizedVector($request, 'hotspot');

        if (null !== $hotspot && null !== $crop) {
            // Get width and height with ratio
            $size = $this->getCroppedWidthHeight($image, $crop);
            $width = $size->getRoundedX();
            $height = $size->getRoundedY();
            // Get point X and Y for crop image based on hotspot
            $offset = $this->resolveCropOffset($image, $width, $height, $hotspot);

            // Debug mode draw crop and center point on image
            if ($this->debug && $request->query->has('trace')) {
                $center_x = (int) round($hotspot->getX() * $image->width());
                $center_y = (int) round($hotspot->getY() * $image->height());

                $x1 = (int) min($image->width() - $width, max(0, $center_x - ($width / 2)));
                $y1 = (int) min($image->height() - $height, max(0, $center_y - ($height / 2)));

                // Draw rectangle on final crop
                $image->drawRectangle($x1, $y1, function (RectangleFactory $rectangle) use ($width, $height) {
                    $rectangle->width($width);
                    $rectangle->height($height);
                    $rectangle->border('#0000FF', 3);
                });

                // Draw green ellipse in center
                $image->drawEllipse($center_x, $center_y, function (EllipseFactory $ellipse) {
                    $ellipse->border('#0FF000', 3);
                    $ellipse->size(30, 30);
                });

                return;
            }
            $image->crop($width, $height, $offset->getRoundedX(), $offset->getRoundedY());
        }
    }

    private function resolveCropOffset(ImageInterface $image, int $width, int $height, Vector $hotspot): Vector
    {
        $offset_x = (int) round(($image->width() * $hotspot->getX()) - ($width / 2));
        $offset_y = (int) round(($image->height() * $hotspot->getY()) - ($height / 2));

        $max_offset_x = max(0, $image->width() - $width);
        $max_offset_y = max(0, $image->height() - $height);

        $offset_x = max(0, $offset_x);
        $offset_y = max(0, $offset_y);
        $offset_x = min($max_offset_x, $offset_x);
        $offset_y = min($max_offset_y, $offset_y);

        return new Vector(
            $offset_x,
            $offset_y,
        );
    }
}
