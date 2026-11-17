<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Child of {@see BrokenOwnerStub} referring back to it.
 */
final class CyclicChildStub
{
    public int $id;

    public ?BrokenOwnerStub $owner = null;
}
