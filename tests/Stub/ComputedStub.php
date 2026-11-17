<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class ComputedStub
{
    public string $name;

    public string $greeting {
        get => 'Hello ' . $this->name;
    }
}
