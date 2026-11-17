<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

enum AliasStatusStub: string
{
    #[MapAlias(['on', 'active'])]
    case Enabled = 'enabled';

    case Disabled = 'disabled';
}
