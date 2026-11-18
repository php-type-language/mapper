<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\Extractor\LiteralTypeExtractor;
use TypeLang\Mapper\Type\LiteralType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Expands one value into its literal: of the value of a backed case or of
 * the name of a unit case, such as for a constant.
 *
 * ```
 * // Status::Active
 * 'active'
 * ```
 */
final class LiteralTypeExpander implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        if (!$type instanceof LiteralType) {
            return null;
        }

        $value = $type->value;

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if ($value instanceof \UnitEnum) {
            $value = $value->name;
        }

        return LiteralTypeExtractor::extract($value);
    }
}
