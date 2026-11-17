<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests\Stub;

final class StringableStub implements \Stringable
{
    public function __toString(): string
    {
        return '42';
    }
}
