<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

enum AliasedSuitStub
{
    #[MapAlias('spades')]
    case Spades;

    case Hearts;
}
