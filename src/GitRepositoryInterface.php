<?php

declare(strict_types=1);

namespace MichaelPetri\Git;

use MichaelPetri\GenericList\ImmutableList;
use MichaelPetri\Git\Exception\FileNotAdded;
use MichaelPetri\Git\Exception\FileNotCommitted;
use MichaelPetri\Git\Exception\FileNotRemoved;
use MichaelPetri\Git\Exception\RepositoryNotInitialized;
use MichaelPetri\Git\Exception\StatusNotFound;
use MichaelPetri\Git\Value\Change;
use MichaelPetri\Git\Value\File;

interface GitRepositoryInterface
{
    /** @throws RepositoryNotInitialized */
    public function init(): void;

    /**
     * @psalm-return ImmutableList<Change>
     *
     * @throws StatusNotFound
     */
    public function status(): ImmutableList;

    /** @throws FileNotAdded */
    public function add(File $file): void;

    /** @throws FileNotRemoved */
    public function remove(File $file, bool $cached = false): void;

    /**
     * @psalm-param non-empty-string $message
     *
     * @throws FileNotCommitted
     */
    public function commit(string $message, ?File $file = null): void;

    public function reset(?File $file = null): void;
}
