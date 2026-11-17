<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Native unions with the words the parser reads as literals.
 */
final class NativeUnionStub
{
    public int|float|string|null $value = null;

    public int|false $limit = false;
}
