<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

enum BrokenAliasStub: string
{
    #[MapAlias('b', 'x')]
    case A = 'a';

    #[MapAlias(1, 'x')]
    case B = 'b';
}
