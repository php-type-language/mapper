<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type\Stub;

final class StringableObject implements \Stringable
{
    public function __toString(): string
    {
        return '<EXAMPLE>';
    }
}
