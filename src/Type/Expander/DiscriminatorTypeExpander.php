<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\DiscriminatorType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Expands a polymorphic class into `object`: an object of a class of the
 * map is given by the data of an object.
 */
final class DiscriminatorTypeExpander implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        if (!$type instanceof DiscriminatorType) {
            return null;
        }

        return new NamedTypeNode(new Name('object'));
    }
}
