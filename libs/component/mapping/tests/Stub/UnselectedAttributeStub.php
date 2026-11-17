<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;

final class UnselectedAttributeStub
{
    public int $id = 0;

    #[MapName('secret_key')]
    protected string $secret = 'secret';

    #[MapType('positive-int')]
    // @phpstan-ignore-next-line the property is only read by reflection
    private int $hidden = 1;

    #[MapType('int')]
    public static int $counter = 0;

    protected string $skipped = '';
}
