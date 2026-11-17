<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;

#[OnTypeError('Person is expected, {{value}} given')]
final class MessageStub
{
    #[OnTypeError('Age must be a number, {{value}} given')]
    public int $age;

    #[OnUndefinedError('Field {{field}} is required')]
    public string $name;
}
