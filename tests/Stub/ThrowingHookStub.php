<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class ThrowingHookStub
{
    public string $greeting {
        get => throw new \DomainException('No greeting');
    }
}
