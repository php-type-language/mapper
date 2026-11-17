<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Recursive type: a node refers to the next one.
 */
final class NodeStub
{
    public string $name;

    public ?NodeStub $next;
}
