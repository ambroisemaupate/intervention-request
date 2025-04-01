<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

use AM\InterventionRequest\Vector;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\Request;

final readonly class HotspotProcessor implements Processor
{
    public function __construct(
        private ?bool $debug = false,
    ) {
    }

    public function process(Image $image, Request $request): void
    {
        $crop = CropProcessor::validateCrop($request);
        if (
            $request->query->has('hotspot')
            && ($request->query->has('width') || $request->query->has('height'))
            && 1 === preg_match('#^(0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)$#', (string) ($request->query->get('hotspot') ?? ''), $hotspot)
            && 0 < count($crop)
        ) {
            $hotspot = new Vector(
                $hotspot[1],
                $hotspot[2]
            );
            // Get width and height with ratio
            $crop = new Vector(
                $crop[0],
                $crop[1]
            );
            $size = $this->getWidthHeight($image, $crop);
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

                $x2 = (int) max($width, min($image->width(), $center_x + ($width / 2)));
                $y2 = (int) max($height, min($image->height(), $center_y + ($height / 2)));

                /*
                 * Upgrade Intervention Image to 3.x
                 * rectangle() is now handled by drawRectangle()
                 * @see https://image.intervention.io/v3/modifying/drawing#drawing-a-rectangle
                 */
                // Draw rectangle on final crop
                $image->rectangle($x1, $y1, $x2, $y2, function ($draw) {
                    $draw->border(3, '#0000FF');
                });

                /*
                 * Upgrade Intervention Image to 3.x
                 * ellipse() is now handled by drawEllipse()
                 * @see https://image.intervention.io/v3/modifying/drawing#drawing-ellipses
                 */
                // Draw green ellipse in center
                $image->ellipse(30, 30, $center_x, $center_y, function ($draw) {
                    $draw->border(3, '#0FF000');
                });

                return;
            }
            $image->crop($width, $height, $offset->getRoundedX(), $offset->getRoundedY());
        }
    }

    private function getWidthHeight(Image $image, Vector $crop): Vector
    {
        $cropX = $crop->getRoundedX();
        $cropY = $crop->getRoundedY();
        // Square ratio
        if ($cropX == $cropY) {
            $width = $height = min($image->width(), $image->height());
        } elseif ($cropX > $cropY) { // Horizontal ratio
            $width = $image->width();
            $height = (int) round(($image->width() * $cropY) / $cropX);
        } else { // Vertical ratio
            $width = (int) round(($image->height() * $cropX) / $cropY);
            $height = $image->height();
        }

        return new Vector(
            $width,
            $height
        );
    }

    private function resolveCropOffset(Image $image, int $width, int $height, Vector $hotspot): Vector
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
