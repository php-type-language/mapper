<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php85;

final class ClosureDefaultStub
{
    public function __construct(
        public \Closure $callback = static function (): int {
            return 42;
        },
    ) {}
}
