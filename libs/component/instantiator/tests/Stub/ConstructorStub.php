<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Stub;

final class ConstructorStub
{
    public bool $isConstructed = false;

    public function __construct(
        public readonly int $amount,
        public readonly ?string $comment = null,
        public readonly string $currency = 'USD',
    ) {
        $this->isConstructed = true;
    }
}
