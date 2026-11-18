<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Type\LogicalTypeNode\UnionTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Expands a built union into the union of the expansions of its members,
 * such as of the values of `value-of<T>`. A union with a member no expander
 * supports is not expanded.
 */
final class UnionTypeExpander implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        if (!$type instanceof UnionType) {
            return null;
        }

        $members = [];

        foreach ($type->types as $member) {
            $expansion = $context->expandType($member);

            if ($expansion === null) {
                return null;
            }

            $members[] = $expansion;
        }

        if (\count($members) === 1) {
            return $members[0];
        }

        return new UnionTypeNode($members);
    }
}
