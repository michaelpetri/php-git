<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Value;

/** @psalm-immutable */
final class File
{
    /** @psalm-param non-empty-string $name */
    private function __construct(
        public readonly Directory $directory,
        public readonly string    $name,
    ) {
    }

    /** @psalm-return non-empty-string */
    public function toString(): string
    {
        return \rtrim($this->directory->path, \DIRECTORY_SEPARATOR) .
            \DIRECTORY_SEPARATOR .
            $this->name;
    }

    public static function from(mixed $path): File
    {
        if (!\is_string($path)) {
            throw new \InvalidArgumentException(\sprintf('Can only create file from string, got "%s"', \get_debug_type($path)));
        }

        if ('' === $path) {
            throw new \InvalidArgumentException('File path must be non-empty-string');
        }

        $filename = \basename($path);
        if ('' === $filename) {
            throw new \InvalidArgumentException(\sprintf('Could not parse filename from input "%s"', $path));
        }

        return new self(
            Directory::from(\dirname($path)),
            $filename
        );
    }
}
