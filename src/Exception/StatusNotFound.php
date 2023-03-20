<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Exception;

use MichaelPetri\Git\Value\Directory;

final class StatusNotFound extends GitException
{
    private function __construct(
        public readonly Directory $directory,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            \sprintf('Could not get status from repository in "%s"', $this->directory->path),
            0,
            $previous
        );
    }

    public static function fromDirectory(Directory $directory, ?\Throwable $previous = null): self
    {
        return new self(
            $directory,
            $previous
        );
    }
}
