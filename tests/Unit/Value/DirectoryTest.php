<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Value;

use MichaelPetri\Git\Value\Directory;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Value\Directory */
final class DirectoryTest extends TestCase
{
    private string $basePath = '/tmp';

    public function testFrom(): void
    {
        self::assertEquals(
            $this->basePath,
            Directory::from($this->basePath)->path
        );
    }

    public function testFromRemovesTailingSlashes(): void
    {
        self::assertEquals(
            $this->basePath,
            Directory::from($this->basePath . \DIRECTORY_SEPARATOR)->path
        );
    }

    public function testFromDoesNotRemoveTailingSlashWhenRootDirectory(): void
    {
        self::assertEquals(
            '/',
            Directory::from('/')->path
        );
    }

    public function testSub(): void
    {
        self::assertEquals(
            $this->basePath . \DIRECTORY_SEPARATOR . 'sub-directory',
            Directory::from($this->basePath . \DIRECTORY_SEPARATOR)->sub('sub-directory')->path
        );
    }

    public function testFailToCreateFromEmptyString(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('Directory path must be non-empty-string')
        );

        Directory::from('');
    }

    public function testFailToCreateFromFloat(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('Can only create directory from string, got "float"')
        );

        Directory::from(1 / 2);
    }
}
