<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of a node, called once per type.
 *
 * ```
 * public function tryBuild(TypeNode $node, BuildContext $context): ?UuidType
 * {
 *     if ($this->pattern->findArguments($node) === null) {
 *         return null;
 *     }
 *
 *     $string = new NamedTypeNode(new Name('string'));
 *
 *     return new UuidType($context->types->getType($string));
 * }
 * ```
 *
 * @template-covariant T
 */
interface TypeBuilderInterface
{
    /**
     * Returns {@see null} in case of the node is not supported by the
     * builder. Nested types are taken from {@see BuildContext::$types}.
     *
     * @return TypeInterface<T>|null
     * @throws TypeDefinitionException in case of the node is supported, but
     *         declared wrong
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface;
}
