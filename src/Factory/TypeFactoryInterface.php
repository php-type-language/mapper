<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Factory;

use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Parser\Node\NodeInterface;

/**
 * @template T of object
 *
 * @template-extends OptionalTypeFactoryInterface<T>
 */
interface TypeFactoryInterface extends OptionalTypeFactoryInterface
{
    /**
     * Creates a new instance of the type {@see T} by the given
     * {@see NodeInterface} AST node object or throws the
     * {@see MapperExceptionInterface} instead.
     *
     * @param TypeFactoryInterface<T> $factory A factory for creating child types
     *       containing all registered factories.
     *
     * @return T Returns {@see T} in case of successful parsing or throws
     *         the {@see MapperExceptionInterface} exception instead.
     * @throws MapperExceptionInterface In case of mapping error.
     */
    public function create(NodeInterface $node, TypeFactoryInterface $factory): object;
}
