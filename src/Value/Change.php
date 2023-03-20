<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Value;

/** @psalm-immutable */
final class Change
{
    public function __construct(
        public readonly File $file,
        public readonly Status $index,
        public readonly Status $workingTree,
    ) {
    }
}
