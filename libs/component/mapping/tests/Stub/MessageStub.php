<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;

#[OnTypeError('Person is expected')]
#[NormalizeAsArray(false)]
final class MessageStub
{
    #[OnTypeError('Age must be a number')]
    #[OnUndefinedError('Age is required')]
    public int $age;

    #[OnTypeError('')]
    public string $name;
}
