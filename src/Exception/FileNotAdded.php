<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Exception;

use MichaelPetri\Git\Value\File;

final class FileNotAdded extends GitException
{
    private function __construct(
        public readonly File $fileToAdd,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            \sprintf('Could not add file "%s" to repository', $this->fileToAdd->toString()),
            0,
            $previous
        );
    }

    public static function fromFile(File $file, ?\Throwable $previous = null): self
    {
        return new self(
            $file,
            $previous
        );
    }
}
