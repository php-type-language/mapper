<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

enum UnitAliasStub
{
    #[MapAlias(['on', 'yes'])]
    case Enabled;

    case Disabled;
}
