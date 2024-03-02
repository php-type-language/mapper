<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Factory;

use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Parser\Node\NodeInterface;

/**
 * @template T of object
 */
interface OptionalTypeFactoryInterface
{
    /**
     * Creates a new instance of the type {@see T} by the given
     * {@see NodeInterface} AST node object or {@see null} instead.
     *
     * @param TypeFactoryInterface<T> $factory A factory for creating child types
     *       containing all registered factories.
     *
     * @return T|null Returns {@see T} in case of successful parsing
     *         or {@see null} instead.
     *
     * @throws MapperExceptionInterface May throws while unknown type creating exception.
     */
    public function create(NodeInterface $node, TypeFactoryInterface $factory): ?object;
}
