<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Exception;

use MichaelPetri\Git\Exception\FileNotAdded;
use MichaelPetri\Git\Value\File;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Exception\FileNotAdded */
final class FileNotAddedTest extends TestCase
{
    public function testInDirectory(): void
    {
        $previous = new \Exception('Previous error');
        $file = File::from('/file');

        $e = FileNotAdded::fromFile($file, $previous);

        self::assertEquals(
            \sprintf('Could not add file "%s" to repository', $file->toString()),
            $e->getMessage()
        );
        self::assertSame($previous, $e->getPrevious());
        self::assertSame($file, $e->fileToAdd);
    }
}
