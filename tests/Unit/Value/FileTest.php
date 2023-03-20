<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Value;

use MichaelPetri\Git\Value\File;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Value\File */
final class FileTest extends TestCase
{
    private string $basePath = '/tmp';

    public function testFrom(): void
    {
        $name = 'example-file-name';
        $file = File::from($this->basePath.\DIRECTORY_SEPARATOR.$name);

        self::assertEquals($this->basePath, $file->directory->path);
        self::assertEquals($name, $file->name);
    }

    public function testFailToCreateFromEmptyString(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('File path must be non-empty-string')
        );

        File::from('');
    }

    public function testFailToCreateFromRootDirectoryPath(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('Could not parse filename from input "/"')
        );

        File::from('/');
    }

    public function testFailToCreateFromFloat(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('Can only create file from string, got "float"')
        );

        File::from(1 / 2);
    }

    public function testToString(): void
    {
        $path = $this->basePath.\DIRECTORY_SEPARATOR.'example-file-name';
        $file = File::from($path);

        self::assertEquals($path, $file->toString());
    }
}
