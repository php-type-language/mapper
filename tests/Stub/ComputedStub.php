<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;

final class ComputedStub
{
    public string $name;

    public string $greeting {
        #[MapGetter]
        get => 'Hello ' . $this->name;
    }
}
