<?php

declare(strict_types=1);

namespace MichaelPetri\Git\Value;

/** @psalm-immutable */
enum Status
{
    case UNTRACKED;
    case MODIFIED;
    case UNMODIFIED;
    case ADDED;
    case DELETED;
    case RENAMED;
    case TYPE_CHANGED;
    case COPIED;
    case UPDATED_BUT_UNMERGED;
    case IGNORED;
}
