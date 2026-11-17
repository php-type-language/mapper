<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class ThrowingDefaultStub
{
    public function __construct(
        public ThrowingConstructorStub $value = new ThrowingConstructorStub(),
    ) {}
}
