<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Exception;

use MichaelPetri\Git\Exception\FileNotCommitted;
use MichaelPetri\Git\Value\File;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Exception\FileNotCommitted */
final class FileNotCommittedTest extends TestCase
{
    public function testInDirectory(): void
    {
        $previous = new \Exception('Previous error');
        $file = File::from('/file');

        $e = FileNotCommitted::fromDirectoryAndFiles($file->directory, [$file], $previous);

        self::assertEquals(
            'Could not commit files to repository',
            $e->getMessage()
        );
        self::assertSame($previous, $e->getPrevious());
        self::assertSame($file->directory, $e->directory);
        self::assertSame([$file], $e->files);
    }
}
