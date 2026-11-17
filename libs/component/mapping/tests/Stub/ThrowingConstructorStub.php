<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class ThrowingConstructorStub
{
    public function __construct()
    {
        throw new \DomainException('Constructor has failed');
    }
}
