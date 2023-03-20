<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Exception;

use MichaelPetri\Git\Value\File;

final class FileNotRemoved extends GitException
{
    private function __construct(
        public readonly File $fileToRemove,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            \sprintf('Could not remove file "%s" from repository', $this->fileToRemove->toString()),
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
