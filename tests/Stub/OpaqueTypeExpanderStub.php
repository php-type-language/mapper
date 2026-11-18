<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Expands {@see OpaqueTypeStub} into `address`.
 */
final class OpaqueTypeExpanderStub implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        if (!$type instanceof OpaqueTypeStub) {
            return null;
        }

        return new NamedTypeNode(new Name('address'));
    }
}
