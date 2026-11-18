<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Expands a built type into the type of the data it takes, such as a class
 * into `object{...}` of its fields or an enum into the union of the values
 * of its cases. The name of the type is replaced by the expansion wherever
 * it is written, see {@see TypeExpander}.
 *
 * ```
 * public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
 * {
 *     if (!$type instanceof UuidType) {
 *         return null;
 *     }
 *
 *     return new NamedTypeNode(new Name('string'));
 * }
 * ```
 */
interface TypeExpanderInterface
{
    /**
     * Returns {@see null} in case of the type is not supported by the
     * expander, the next one is tried.
     *
     * @param TypeInterface<mixed> $type
     */
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode;
}
