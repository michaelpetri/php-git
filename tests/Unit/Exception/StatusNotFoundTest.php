<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Exception;

use MichaelPetri\Git\Exception\StatusNotFound;
use MichaelPetri\Git\Value\Directory;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Exception\StatusNotFound */
final class StatusNotFoundTest extends TestCase
{
    public function testInDirectory(): void
    {
        $previous = new \Exception('Previous error');
        $directory = Directory::from('/');

        $e = StatusNotFound::fromDirectory($directory, $previous);

        self::assertEquals(
            \sprintf('Could not get status from repository in "%s"', $directory->path),
            $e->getMessage()
        );
        self::assertSame($previous, $e->getPrevious());
        self::assertSame($directory, $e->directory);
    }
}
