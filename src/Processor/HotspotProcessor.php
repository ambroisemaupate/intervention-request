<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Processor;

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
            && $request->query->has('crop')
            && ($request->query->has('width') || $request->query->has('height'))
            && 1 === preg_match('#^(0(?:\.\d+)?|1(?:\.0+)?)[:;x](0(?:\.\d+)?|1(?:\.0+)?)$#', (string) ($request->query->get('hotspot') ?? ''), $hotspot)
            && 0 < count($crop)
        ) {
            $hotspot = [
                $hotspot[0],
                floatval($hotspot[1]),
                floatval($hotspot[2]),
            ];
            // Get width and height with ratio
            [$width, $height] = $this->getWidthHeight($image, $crop);
            // Get point X and Y for crop image based on hotspot
            [$offset_x, $offset_y] = $this->resolveCropOffset($image, $width, $height, $hotspot);

            // Debug mode draw crop and center point on image
            if ($this->debug) {
                $center_x = (int) round($hotspot[1] * $image->width());
                $center_y = (int) round($hotspot[2] * $image->height());

                $x1 = (int) min($image->width() - $width, max(0, $center_x - ($width / 2)));
                $y1 = (int) min($image->height() - $height, max(0, $center_y - ($height / 2)));

                $x2 = (int) max($width, min($image->width(), $center_x + ($width / 2)));
                $y2 = (int) max($height, min($image->height(), $center_y + ($height / 2)));

                /**
                 * Upgrade Intervention Image to 3.x
                 * rectangle() is now handled by drawRectangle()
                 * @see https://image.intervention.io/v3/modifying/drawing#drawing-a-rectangle
                 */
                // Draw rectangle on final crop
                $image->rectangle($x1, $y1, $x2, $y2, function ($draw) {
                    $draw->border(3, '#0000FF');
                });

                /**
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
            $image->crop($width, $height, $offset_x, $offset_y);
        }
    }

    /**
     * @param array{int, int} $crop
     *
     * @return array{int, int}
     */
    private function getWidthHeight(Image $image, array $crop): array
    {
        // Square ratio
        if ($crop[0] == $crop[1]) {
            $width = $height = min($image->width(), $image->height());
        } elseif ($crop[0] > $crop[1]) { // Horizontal ratio
            $width = $image->width();
            $height = (int) round(($image->width() * $crop[1]) / $crop[0]);
        } else { // Vertical ratio
            $width = (int) round(($image->height() * $crop[0]) / $crop[1]);
            $height = $image->height();
        }

        return [$width, $height];
    }

    /**
     * @param array{string, float, float} $hotspot
     *
     * @return array{int, int}
     */
    private function resolveCropOffset(Image $image, int $width, int $height, array $hotspot): array
    {
        $offset_x = (int) (($image->width() * $hotspot[1]) - ($width / 2));
        $offset_y = (int) (($image->height() * $hotspot[2]) - ($height / 2));

        $max_offset_x = $image->width() - $width;
        $max_offset_y = $image->height() - $height;

        if ($offset_x < 0) {
            $offset_x = 0;
        }

        if ($offset_y < 0) {
            $offset_y = 0;
        }

        if ($offset_x > $max_offset_x) {
            $offset_x = $max_offset_x;
        }

        if ($offset_y > $max_offset_y) {
            $offset_y = $max_offset_y;
        }

        return [$offset_x, $offset_y];
    }
}
