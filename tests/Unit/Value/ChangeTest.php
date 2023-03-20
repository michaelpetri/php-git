<?php

declare(strict_types=1);

namespace Tests\MichaelPetri\Git\Unit\Value;

use MichaelPetri\Git\Value\Change;
use MichaelPetri\Git\Value\File;
use MichaelPetri\Git\Value\Status;
use PHPUnit\Framework\TestCase;

/** @covers \MichaelPetri\Git\Value\Change */
final class ChangeTest extends TestCase
{
    private string $basePath = '/tmp';

    public function testSomething(): void
    {
        $file = File::from('/file');
        $index = Status::UNTRACKED;
        $workingTree = Status::ADDED;

        $change = new Change(
            $file,
            $index,
            $workingTree
        );

        self::assertSame($file, $change->file);
        self::assertSame($index, $change->index);
        self::assertSame($workingTree, $change->workingTree);
    }
}
