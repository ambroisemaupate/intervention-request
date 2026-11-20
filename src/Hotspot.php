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

    /**
     * Strict float comparison is safe: coordinates come from parsed literals, never from arithmetic.
     */
    public function isFullArea(): bool
    {
        return 0.0 === $this->topLeft->getX() && 0.0 === $this->topLeft->getY()
            && 1.0 === $this->bottomRight->getX() && 1.0 === $this->bottomRight->getY();
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
