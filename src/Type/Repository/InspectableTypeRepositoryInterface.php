<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Repository telling how its types are built: the builder of a node and the
 * type as the builder has built it, before the decorators.
 *
 * ```
 * $repository->getType($status);             // TrimmingType
 * $repository->findUndecoratedType($status); // BackedEnumType
 * $repository->findBuilder($status);         // BackedEnumTypeBuilder
 * ```
 */
interface InspectableTypeRepositoryInterface extends TypeRepositoryInterface
{
    /**
     * Returns the type of the node as the builder has built it, {@see null}
     * in case of the decorators do not change it or it is not built yet.
     *
     * @return TypeInterface<mixed>|null
     */
    public function findUndecoratedType(TypeNode $node): ?TypeInterface;

    /**
     * Returns the builder the type of the node is built by, {@see null} in
     * case of it is not built yet.
     *
     * @return TypeBuilderInterface<mixed>|null
     */
    public function findBuilder(TypeNode $node): ?TypeBuilderInterface;
}
