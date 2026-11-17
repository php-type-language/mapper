<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

enum AliasStub: string
{
    #[MapAlias('on')]
    #[MapAlias(['active', 'enabled_v1'])]
    case Enabled = 'enabled';

    case Disabled = 'disabled';
}
