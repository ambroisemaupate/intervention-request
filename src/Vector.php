<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

final class Vector
{
    private float $x;
    private float $y;

    public function __construct(
        int|float|string $x,
        int|float|string $y,
    ) {
        $this->x = floatval($x);
        $this->y = floatval($y);
    }

    public function getX(): float
    {
        return $this->x;
    }

    public function getY(): float
    {
        return $this->y;
    }

    public function getRoundedX(): int
    {
        return (int) round($this->x);
    }

    public function getRoundedY(): int
    {
        return (int) round($this->y);
    }
}
