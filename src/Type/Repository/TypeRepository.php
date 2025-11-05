<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
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

    private TypeRepositoryInterface $context;

    /**
     * @param iterable<mixed, TypeBuilderInterface> $builders
     */
    public function __construct(
        private readonly TypeParserInterface $parser,
        iterable $builders,
    ) {
        $this->context = $this;
        $this->builders = iterable_to_array($builders, false);
    }

    /**
     * @internal internal method for passing the root calling context
     */
    public function setTypeRepository(TypeRepositoryInterface $parent): void
    {
        $this->context = $parent;
    }

    private function buildType(TypeStatement $statement): TypeInterface
    {
        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                // @phpstan-ignore-next-line : Statement expects a bottom type (never), but TypeStatement passed
                return $factory->build($statement, $this->context, $this->parser);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        return $this->buildType($statement);
    }
}
