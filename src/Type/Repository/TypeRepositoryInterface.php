<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Returns the built type of a node, a type is built once.
 *
 * ```
 * // in a builder of `list<T>`
 * $item = $context->types->getType($node->arguments[0]->value);
 * ```
 */
interface TypeRepositoryInterface
{
    /**
     * A type referring to itself while being built, such as a class with a
     * property of the same class, gets a reference resolved on its first
     * use, builders do not handle the recursion.
     *
     * @return TypeInterface<mixed>
     * @throws TypeDefinitionException in case of no builder supports the
     *         node or the type is declared wrong
     */
    public function getType(TypeNode $node): TypeInterface;
}
