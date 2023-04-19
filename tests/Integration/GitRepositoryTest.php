<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Integration;

use MichaelPetri\Git\Exception\FileNotAdded;
use MichaelPetri\Git\Exception\FileNotCommitted;
use MichaelPetri\Git\Exception\FileNotRemoved;
use MichaelPetri\Git\Exception\RepositoryNotInitialized;
use MichaelPetri\Git\Exception\StatusNotFound;
use MichaelPetri\Git\GitRepository;
use MichaelPetri\Git\Value\Change;
use MichaelPetri\Git\Value\Directory;
use MichaelPetri\Git\Value\Duration;
use MichaelPetri\Git\Value\File;
use MichaelPetri\Git\Value\Status;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** @covers \MichaelPetri\Git\GitRepository */
final class GitRepositoryTest extends TestCase
{
    private string $basePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->basePath = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.'GitRepositoryTest';

        // Ensure directory is empty
        $p = new Process(['rm', '-rf', $this->basePath.\DIRECTORY_SEPARATOR.'*']);
        $p->mustRun();
    }

    public function testInit(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = new GitRepository($directory, Duration::inSeconds(60));

        self::assertDirectoryDoesNotExist($directory->path.\DIRECTORY_SEPARATOR.'.git');

        $repository->init();

        self::assertDirectoryExists($directory->path.\DIRECTORY_SEPARATOR.'.git');
    }

    public function testInitFailsWhenDirectoryNotExists(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = new GitRepository($directory, Duration::inSeconds(60));

        $this->delete($directory);

        $this->expectExceptionObject(
            RepositoryNotInitialized::fromDirectory($directory)
        );

        $repository->init();
    }

    public function testAdd(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'existing-uncommitted-file');
        $repository = $this->createRepository($directory);

        $repository->add($file);

        self::assertEquals(
            [
                new Change($file, Status::ADDED, Status::UNMODIFIED),
            ],
            $repository->status()->toArray()
        );
    }

    public function testAddFailsWhenTryToAddFileOutsideRepository(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = $this->createRepository($directory);
        $file = File::from('/another/location');

        $this->expectExceptionObject(
            FileNotAdded::fromFile($file)
        );

        $repository->add($file);
    }

    public function testRemove(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'existing-committed-file');
        $repository = $this->createRepository($directory, [$file]);

        $repository->remove($file);

        self::assertEquals(
            [
                new Change($file, Status::DELETED, Status::UNMODIFIED),
            ],
            $repository->status()->toArray()
        );
    }

    public function testRemoveFromIndex(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'existing-committed-file');
        $repository = $this->createRepository($directory);

        $repository->add($file);
        $repository->remove($file, true);

        self::assertEquals(
            [
                new Change($file, Status::UNTRACKED, Status::UNTRACKED),
            ],
            $repository->status()->toArray()
        );
    }

    public function testRemoveFailsWhenTryToRemoveFileOutsideRepository(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = $this->createRepository($directory);
        $file = File::from('/another/location');

        $this->expectExceptionObject(
            FileNotRemoved::fromFile($file)
        );

        $repository->remove($file);
    }

    public function testCommit(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file1 = $this->createFile($directory, 'existing-uncommitted-file-1');
        $file2 = $this->createFile($directory, 'existing-uncommitted-file-2');
        $file3 = $this->createFile($directory, 'existing-uncommitted-file-3');
        $repository = $this->createRepository($directory);

        self::assertEquals(
            [
                new Change($file1, Status::UNTRACKED, Status::UNTRACKED),
                new Change($file2, Status::UNTRACKED, Status::UNTRACKED),
                new Change($file3, Status::UNTRACKED, Status::UNTRACKED),
            ],
            $repository->status()->toArray()
        );

        $repository->add($file1);
        $repository->add($file2);
        $repository->add($file3);

        $repository->commit('Committed file 3 only.', $file3);

        self::assertEquals(
            [
                new Change($file1, Status::ADDED, Status::UNMODIFIED),
                new Change($file2, Status::ADDED, Status::UNMODIFIED),
            ],
            $repository->status()->toArray()
        );

        $repository->commit('Committed all other files.');

        self::assertEmpty($repository->status()->toArray());
    }

    public function testCommitFailsWhenTryToCommitFileOutsideRepository(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = $this->createRepository($directory);
        $file = File::from('/another/location');

        $this->expectExceptionObject(
            FileNotCommitted::fromDirectoryAndFiles($file->directory, [$file])
        );

        $repository->commit('This will not work', $file);
    }

    public function testStatus(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = $this->createRepository($directory);

        self::assertEquals(
            [],
            $repository->status()->toArray()
        );
    }

    public function testStatusFailsWhenDirectoryNotExists(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = $this->createRepository($directory);

        $this->delete($directory);

        $this->expectExceptionObject(
            StatusNotFound::fromDirectory($directory)
        );

        $repository->status();
    }

    public function testNewFileGetsDetected(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'new-uncommitted-file');
        $repository = $this->createRepository($directory);

        self::assertEquals(
            [
                new Change($file, Status::UNTRACKED, Status::UNTRACKED),
            ],
            $repository->status()->toArray()
        );
    }

    public function testModifiedFileGetsTracked(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'existing-committed-file');
        $repository = $this->createRepository($directory, [$file]);

        self::assertEmpty($repository->status()->toArray());

        $this->write($file, 'New content');

        self::assertEquals(
            [
                new Change($file, Status::UNMODIFIED, Status::MODIFIED),
            ],
            $repository->status()->toArray()
        );
    }

    public function testDeletedFileGetsTracked(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'existing-committed-file');
        $repository = $this->createRepository($directory, [$file]);

        self::assertEmpty($repository->status()->toArray());

        $this->delete($file);

        self::assertEquals(
            [
                new Change($file, Status::UNMODIFIED, Status::DELETED),
            ],
            $repository->status()->toArray()
        );
    }

    public function testResetAllFiles(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file1 = $this->createFile($directory, 'new-uncommitted-file-1');
        $file2 = $this->createFile($directory, 'new-uncommitted-file-2');
        $repository = $this->createRepository($directory);

        self::assertEquals(
            [
                new Change($file1, Status::UNTRACKED, Status::UNTRACKED),
                new Change($file2, Status::UNTRACKED, Status::UNTRACKED)
            ],
            $repository->status()->toArray()
        );

        $repository->add($file1);
        $repository->add($file2);

        self::assertEquals(
            [
                new Change($file1, Status::ADDED, Status::UNMODIFIED),
                new Change($file2, Status::ADDED, Status::UNMODIFIED)
            ],
            $repository->status()->toArray()
        );

        $repository->reset();

        self::assertEquals(
            [
                new Change($file1, Status::UNTRACKED, Status::UNTRACKED),
                new Change($file2, Status::UNTRACKED, Status::UNTRACKED)
            ],
            $repository->status()->toArray()
        );
    }

    public function testResetSingleFile(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $file = $this->createFile($directory, 'new-uncommitted-file');
        $repository = $this->createRepository($directory);

        self::assertEquals(
            [
                new Change($file, Status::UNTRACKED, Status::UNTRACKED)
            ],
            $repository->status()->toArray()
        );

        $repository->add($file);

        self::assertEquals(
            [
                new Change($file, Status::ADDED, Status::UNMODIFIED)
            ],
            $repository->status()->toArray()
        );

        $repository->reset($file);

        self::assertEquals(
            [
                new Change($file, Status::UNTRACKED, Status::UNTRACKED)
            ],
            $repository->status()->toArray()
        );
    }

    public function testTimeout(): void
    {
        $directory = $this->createDirectory(__FUNCTION__);
        $repository = new GitRepository($directory, Duration::inMilliseconds(1));

        $this->expectExceptionObject(
            RepositoryNotInitialized::fromDirectory($directory)
        );

        $repository->init();
    }

    /** @psalm-param non-empty-string $name */
    private function createDirectory(string $name): Directory
    {
        $path = $this->basePath.\DIRECTORY_SEPARATOR.$name;

        $p = new Process(['rm', '-rf', $path]);
        $p->mustRun();

        $p = new Process(['mkdir', '-p', $path]);
        $p->mustRun();

        return Directory::from($path);
    }

    /** @psalm-param non-empty-string $name */
    private function createFile(Directory $directory, string $name): File
    {
        $path = $directory->path.\DIRECTORY_SEPARATOR.$name;

        file_put_contents($path, '');

        return File::from($path);
    }

    /** @psalm-param File $files */
    private function createRepository(Directory $directory, array $files = []): GitRepository
    {
        $p = new Process(['git', 'init'], $directory->path);
        $p->mustRun();

        $p = new Process(['git', 'config', 'user.email', 'SymfonyFilesystemEventReceiver@localhost'], $directory->path);
        $p->mustRun();

        $p = new Process(['git', 'config', 'user.name', 'Symfony Filesystem Event Receiver'], $directory->path);
        $p->mustRun();

        $repository = new GitRepository($directory, Duration::inSeconds(60));

        if ([] === $files) {
            return $repository;
        }

        foreach ($files as $file) {
            $p = new Process(['git', 'add', $file->toString()], $directory->path);
            $p->mustRun();
        }

        $p = new Process(['git', 'commit', '-m', 'Initial commit'], $directory->path);
        $p->mustRun();

        return $repository;
    }

    /** @psalm-param non-empty-string $name */
    private function write(File $file, string $content = ''): void
    {
        \file_put_contents($file->toString(), $content);
    }

    /** @psalm-param non-empty-string $name */
    private function delete(File|Directory $target): void
    {
        if ($target instanceof File) {
            \unlink($target->toString());
        } else {
            $p = new Process(['rm', '-rf', $target->path]);
            $p->mustRun();
        }
    }
}
