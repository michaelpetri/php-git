<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Value;

/** @psalm-immutable */
final class Duration
{
    private function __construct(
        public readonly int|float $seconds
    ) {
    }

    /** @psalm-param positive-int $value */
    public static function inMilliseconds(int $value): self
    {
        return new self($value / 1000);
    }

    /** @psalm-param positive-int $value */
    public static function inSeconds(int $value): self
    {
        return new self($value);
    }
}
