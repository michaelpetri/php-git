<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Exception;

use MichaelPetri\Git\Exception\FileNotRemoved;
use MichaelPetri\Git\Value\File;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Exception\FileNotRemoved */
final class FileNotRemovedTest extends TestCase
{
    public function testInDirectory(): void
    {
        $previous = new \Exception('Previous error');
        $file = File::from('/file');

        $e = FileNotRemoved::fromFile($file, $previous);

        self::assertEquals(
            \sprintf('Could not remove file "%s" from repository', $file->toString()),
            $e->getMessage()
        );
        self::assertSame($previous, $e->getPrevious());
        self::assertSame($file, $e->fileToRemove);
    }
}
