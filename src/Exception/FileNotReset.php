<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Exception;

use MichaelPetri\Git\Value\Directory;
use MichaelPetri\Git\Value\File;
use Throwable;

final class FileNotReset extends GitException
{
    /** @psalm-param list<File> $files */
    private function __construct(
        public readonly Directory $directory,
        public readonly array     $files,
        ?Throwable                $previous = null
    ) {
        parent::__construct(
            'Could not reset files in repository',
            0,
            $previous
        );
    }

    /** @psalm-param list<File> $files */
    public static function fromDirectoryAndFiles(Directory $directory, array $files, ?Throwable $previous = null): self
    {
        return new self(
            $directory,
            $files,
            $previous
        );
    }
}
