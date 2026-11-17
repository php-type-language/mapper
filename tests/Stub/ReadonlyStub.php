<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class ReadonlyStub
{
    public function __construct(
        public readonly string $value,
    ) {}
}
