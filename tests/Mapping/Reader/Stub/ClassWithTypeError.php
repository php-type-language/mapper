<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\OnTypeError;

#[OnTypeError('Custom class type error')]
final class ClassWithTypeError
{
    public int $value;
}
