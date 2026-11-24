<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\Tests\Stub\Concern\AccessorTrait;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub as Owner;

/**
 * Getters of a trait, one overridden by the class.
 */
final class TraitAccessorStub
{
    use AccessorTrait;

    #[MapGetter('owner', type: '?Owner')]
    public function getOwner(): mixed
    {
        return null;
    }
}
