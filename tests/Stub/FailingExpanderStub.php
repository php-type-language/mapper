<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Expander with a bug: fails on every type.
 */
final class FailingExpanderStub implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        throw new \TypeError('Bug of the expander');
    }
}
