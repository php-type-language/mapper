<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\DateTimeType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Expands a date into `string`: a date is given by its string.
 */
final class DateTimeTypeExpander implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        if (!$type instanceof DateTimeType) {
            return null;
        }

        return new NamedTypeNode(new Name('string'));
    }
}
