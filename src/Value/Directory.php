<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Value;

/** @psalm-immutable */
final class Directory
{
    /** @psalm-param non-empty-string $path */
    private function __construct(
        public readonly string $path
    ) {
    }

    /** @psalm-param non-empty-string $name */
    public function sub(string $name): self
    {
        return self::from(
            $this->path . \DIRECTORY_SEPARATOR .$name
        );
    }

    /** @psalm-pure */
    public static function from(mixed $value): self
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Can only create directory from string, got "%s"', get_debug_type($value)));
        }

        if ('/' !== $value) {
            $value = \rtrim($value, \DIRECTORY_SEPARATOR);
        }

        $value = \trim($value);

        if ('' === $value) {
            throw new \InvalidArgumentException('Directory path must be non-empty-string');
        }

        return new self($value);
    }
}
