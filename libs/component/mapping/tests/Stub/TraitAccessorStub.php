<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\Concern\AccessorTrait;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub as Owner;

final class TraitAccessorStub
{
    use AccessorTrait;

    public mixed $owner = null;

    public mixed $parent = null;

    #[MapGetter('owner')]
    #[MapType('?Owner')]
    public function getOwner(): mixed
    {
        return null;
    }
}
