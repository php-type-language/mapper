<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

final class AliasConstantStub
{
    #[MapAlias('x')]
    public const A = 'a';
}
