<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;

final class ThrowingHookStub
{
    public string $greeting {
        #[MapGetter]
        get => throw new \DomainException('No greeting');
    }
}
