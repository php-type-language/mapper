<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\OnUndefinedError;

/**
 * Empty arguments of the access, helpers of a method and of a direction the
 * member does not take part in.
 */
final class WrongArgumentStub
{
    #[MapGetter(name: '', type: ''), MapSetter(type: 'int', onTypeError: '')]
    public int $id = 0;

    #[MapGetter, MapAlias('alias')]
    public int $read = 0;

    #[MapGetter('name'), MapName('other')]
    public function getName(): string
    {
        return '';
    }

    #[MapSetter('name'), OnUndefinedError('Name is required')]
    public function setName(string $name): void {}
}
