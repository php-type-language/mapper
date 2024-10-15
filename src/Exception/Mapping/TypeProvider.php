<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Traverser;
use TypeLang\Parser\Traverser\TypeMapVisitor;

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

    public function explain(callable $transform): self
    {
        Traverser::through(
            visitor: new TypeMapVisitor($transform(...)),
            nodes: [$this->expected],
        );

        return $this;
    }
}
