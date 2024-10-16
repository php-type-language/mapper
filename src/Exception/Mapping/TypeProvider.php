<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @internal this is an internal library trait, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Exception\Mapping
 *
 * @phpstan-require-implements MappingExceptionInterface
 *
 * @mixin MappingExceptionInterface
 */
trait TypeProvider
{
    protected readonly TypeStatement $expected;

    public function getExpectedType(): TypeStatement
    {
        return $this->expected;
    }
}
