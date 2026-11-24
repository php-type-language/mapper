<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Concern;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Owner;

trait AccessorTrait
{
    #[MapGetter('owner', type: '?Owner')]
    public function getOwner(): mixed
    {
        return null;
    }

    #[MapGetter('parent', type: '?Owner')]
    public function getParent(): mixed
    {
        return null;
    }
}
