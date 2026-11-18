<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\BackedEnumType;
use TypeLang\Mapper\Type\Extractor\LiteralTypeExtractor;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumType;
use TypeLang\Type\TypeNode;

/**
 * Expands an enum into the union of the values of its cases without the
 * aliases, an enum without values into the union of the names of its
 * cases. An enum without cases is not expanded.
 *
 * ```
 * // 'active'|'banned'
 * ```
 */
final class EnumTypeExpander implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        $values = [];

        if ($type instanceof BackedEnumType) {
            foreach ($type->class::cases() as $case) {
                $values[] = $case->value;
            }
        }

        if ($type instanceof UnitEnumType) {
            foreach ($type->class::cases() as $case) {
                $values[] = $case->name;
            }
        }

        if ($values === []) {
            return null;
        }

        return LiteralTypeExtractor::extractAll($values);
    }
}
