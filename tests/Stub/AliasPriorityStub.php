<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

enum AliasPriorityStub: int
{
    #[MapAlias(10)]
    case High = 1;

    case Low = 2;
}
