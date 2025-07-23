<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

final readonly class Hotspot
{
    public function __construct(
        public Vector $center,
        public Vector $topLeft,
        public Vector $bottomRight,
    ) {
    }

    public static function point(Vector $center): self
    {
        return new self(
            $center,
            new Vector(0, 0),
            new Vector(1, 1),
        );
    }
}
