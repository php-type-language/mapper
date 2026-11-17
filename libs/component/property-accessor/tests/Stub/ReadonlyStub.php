<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

final class ReadonlyStub
{
    public readonly string $readonlyProperty;

    public function __construct(string $value = 'readonly-value')
    {
        $this->readonlyProperty = $value;
    }
}
