<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;

final class StrictAttributeStub
{
    #[MapType('positive-int', strict: true)]
    public int $value = 1;
}
