<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Concern;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Owner;

trait AccessorTrait
{
    #[MapGetter('owner')]
    #[MapType('?Owner')]
    public function getOwner(): mixed
    {
        return null;
    }

    #[MapGetter('parent')]
    #[MapType('?Owner')]
    public function getParent(): mixed
    {
        return null;
    }
}
