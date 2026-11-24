<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;

final class UntypedGetterStub
{
    // @phpstan-ignore-next-line the getter is untyped on purpose
    #[MapGetter('value')]
    public function getValue()
    {
        return 1;
    }
}
