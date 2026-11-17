<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\OnTypeError;

/**
 * Property with its own message over a class with messages of its fields.
 */
final class MessageOwnerStub
{
    #[OnTypeError('Person is invalid')]
    public MessageStub $person;
}
