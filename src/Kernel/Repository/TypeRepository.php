<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Context\MapperContext;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

use function TypeLang\Mapper\iterable_to_array;

final class TypeRepository implements
    TypeRepositoryInterface,
    TypeRepositoryDecoratorInterface
{
    /**
     * @var list<TypeBuilderInterface>
     */
    private array $builders = [];

    private TypeRepositoryInterface $repository;

    /**
     * @param iterable<mixed, TypeBuilderInterface> $builders
     */
    public function __construct(
        private readonly MapperContext $context,
        private readonly DirectionInterface $direction,
        iterable $builders,
    ) {
        $this->repository = $this;
        $this->builders = iterable_to_array($builders, false);
    }

    /**
     * @internal internal method for passing the root calling context
     */
    public function setTypeRepository(TypeRepositoryInterface $parent): void
    {
        $this->repository = $parent;
    }

    private function buildType(TypeStatement $statement): TypeInterface
    {
        $context = BuildingContext::createFromMapperContext(
            context: $this->context,
            direction: $this->direction,
            types: $this->repository,
        );

        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                return $factory->build($statement, $context);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        return $this->buildType($statement);
    }
}
