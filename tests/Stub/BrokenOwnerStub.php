<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Type that cannot be built: its last property is of a missing class, the
 * first one refers back to it through {@see CyclicChildStub}.
 */
final class BrokenOwnerStub
{
    public CyclicChildStub $child;

    public MissingClassStub $missing;
}
