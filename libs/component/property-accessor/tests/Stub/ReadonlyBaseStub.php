<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

class ReadonlyBaseStub
{
    public function __construct(
        public readonly string $promoted = 'promoted-value',
    ) {}
}
