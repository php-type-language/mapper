<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Type\Expander\TypeExpander;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Place of a type in the type being expanded: expands its nested types.
 * The limits of the depth and of the fields are kept by the expansion, an
 * expander describes the whole type.
 *
 * ```
 * public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
 * {
 *     // ...
 *     $field = $context->expand($type->declaredType);
 * }
 * ```
 */
final class ExpandContext
{
    public function __construct(
        private readonly TypeExpander $expander,
        /**
         * Level of the expanded type
         *
         * @var int<0, max>
         */
        private readonly int $depth,
    ) {}

    /**
     * Expands a nested type as it is written, such as the declared type of
     * property, one level deeper.
     */
    public function expand(TypeNode $type): TypeNode
    {
        return $this->expander->expand($type, $this->depth + 1);
    }

    /**
     * Expands a built type at the same level, such as a member of a union.
     * {@see null} in case of no expander supports it.
     *
     * @param TypeInterface<mixed> $type
     */
    public function expandType(TypeInterface $type): ?TypeNode
    {
        return $this->expander->expandType($type, $this->depth);
    }
}
