<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

class HolderUnion
{
    public Square|Circle $shape;
}
