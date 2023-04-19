<?php

declare(strict_types=1);

namespace MichaelPetri\Git;

use MichaelPetri\GenericList\ImmutableList;
use MichaelPetri\Git\Exception\FileNotAdded;
use MichaelPetri\Git\Exception\FileNotCommitted;
use MichaelPetri\Git\Exception\FileNotRemoved;
use MichaelPetri\Git\Exception\FileNotReset;
use MichaelPetri\Git\Exception\RepositoryNotInitialized;
use MichaelPetri\Git\Exception\StatusNotFound;
use MichaelPetri\Git\Value\Change;
use MichaelPetri\Git\Value\Directory;
use MichaelPetri\Git\Value\Duration;
use MichaelPetri\Git\Value\File;
use MichaelPetri\Git\Value\Status;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

final class GitRepository implements GitRepositoryInterface
{
    public function __construct(
        private readonly Directory $directory,
        private readonly ?Duration $timeout
    ) {
    }

    public function init(): void
    {
        try {
            $this->execute(['git', 'init']);
            $this->execute(['git', 'config', 'user.email', 'php-git@localhost']);
            $this->execute(['git', 'config', 'user.name', 'michaelpetri/php-git']);
        } catch (RuntimeException $e) {
            throw RepositoryNotInitialized::fromDirectory($this->directory, $e);
        }
    }

    public function status(): ImmutableList
    {
        try {
            $output = $this->execute(['git', 'status', '--short']);
        } catch (RuntimeException $e) {
            throw StatusNotFound::fromDirectory($this->directory, $e);
        }

        if (null === $output) {
            return ImmutableList::of();
        }

        // Parse working tree & index status and filename: XY Filename
        \preg_match_all('/^([\?\sAMD])([\?\sAMD])\s([^\n<>:;,?"*|\/]+)$/m', $output, $matches, \PREG_SET_ORDER);

        $changes = [];
        foreach ($matches as $match) {
            $filename = \array_key_exists(3, $match)
                ? \trim($match[3])
                : throw new \InvalidArgumentException('Could not parse filename from git status.');
            $changes[] = new Change(
                File::from($this->directory->path . \DIRECTORY_SEPARATOR . $filename),
                self::parseStatusFlag($match[1] ?? null),
                self::parseStatusFlag($match[2] ?? null)
            );
        }

        return ImmutableList::of(...$changes);
    }

    public function add(File $file): void
    {
        try {
            $this->execute(['git', 'add', $this->getRelativePath($file)]);
        } catch (RuntimeException|\InvalidArgumentException $e) {
            throw FileNotAdded::fromFile($file, $e);
        }
    }

    public function remove(File $file, bool $cached = false): void
    {
        try {
            $command = ['git', 'rm'];

            if ($cached) {
                $command[] = '--cached';
            }

            $command[] = $this->getRelativePath($file);

            $this->execute($command);
        } catch (RuntimeException|\InvalidArgumentException $e) {
            throw FileNotRemoved::fromFile($file, $e);
        }
    }

    public function commit(string $message, ?File $file = null): void
    {
        try {
            $command = ['git', 'commit', '-m', $message];

            if (null !== $file) {
                $command[] = $this->getRelativePath($file);
            }

            $this->execute($command);
        } catch (RuntimeException|\InvalidArgumentException $e) {
            throw FileNotCommitted::fromDirectoryAndFiles(
                $this->directory,
                null === $file
                    ? $this->status()->map(static fn (Change $change): File => $change->file)->toArray()
                    : [$file],
                $e
            );
        }
    }

    public function reset(?File $file = null): void
    {
        try {
            $command = ['git', 'reset'];

            if (null !== $file) {
                $command[] = $this->getRelativePath($file);
            }

            $this->execute($command);
        } catch (RuntimeException|\InvalidArgumentException $e) {
            throw FileNotReset::fromDirectoryAndFiles(
                $this->directory,
                null === $file
                    ? $this->status()->map(static fn (Change $change): File => $change->file)->toArray()
                    : [$file],
                $e
            );
        }
    }

    /** @throws RuntimeException */
    private function execute(array $command): ?string
    {
        $process = new Process(
            $command,
            $this->directory->path,
            null,
            null,
            $this->timeout?->seconds
        );
        $process->mustRun();
        $output = $process->getOutput();

        return '' !== $output
            ? $output
            : null;
    }

    private static function parseStatusFlag(?string $flag): Status
    {
        return match ($flag) {
            ' ' => Status::UNMODIFIED,
            'M' => Status::MODIFIED,
            'A' => Status::ADDED,
            'D' => Status::DELETED,
            'R' => Status::RENAMED,
            'C' => Status::COPIED,
            'U' => Status::UPDATED_BUT_UNMERGED,
            '?' => Status::UNTRACKED,
            '!' => Status::IGNORED,
            null => throw new \InvalidArgumentException('Could not parse git status'),
            default => throw new \InvalidArgumentException(\sprintf('Unknown git status "%s".', $flag)),
        };
    }

    private function getRelativePath(File $file): string
    {
        if (!\str_starts_with($file->toString(), $this->directory->path)) {
            throw new \InvalidArgumentException('The given file is not part of the repository.');
        }

        return \substr(
            \str_replace(
                $this->directory->path,
                '',
                $file->toString()
            ),
            1
        );
    }
}
