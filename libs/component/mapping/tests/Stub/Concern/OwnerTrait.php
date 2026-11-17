<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Concern;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Owner;

trait OwnerTrait
{
    /**
     * @var list<Owner>
     */
    public array $owners = [];

    #[MapType('Owner|self')]
    public ?object $main = null;

    public ?self $parent = null;
}
