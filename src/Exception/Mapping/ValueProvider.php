<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

/**
 * @internal this is an internal library trait, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Exception\Mapping
 *
 * @phpstan-require-implements ValueExceptionInterface
 *
 * @mixin ValueExceptionInterface
 */
trait ValueProvider
{
    protected readonly mixed $value;

    public function getValue(): mixed
    {
        return $this->value;
    }
}
