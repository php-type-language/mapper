<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

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
        $this->builders = self::toArrayList($builders);
    }

    /**
     * @param iterable<mixed, TypeBuilderInterface> $types
     *
     * @return list<TypeBuilderInterface>
     */
    private static function toArrayList(iterable $types): array
    {
        return match (true) {
            $types instanceof \Traversable => \iterator_to_array($types, false),
            \array_is_list($types) => $types,
            default => \array_values($types),
        };
    }

    /**
     * @internal internal method for passing the root calling context
     */
    public function setTypeRepository(TypeRepositoryInterface $parent): void
    {
        $this->context = $parent;
    }

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                // @phpstan-ignore-next-line : Statement expects a bottom type (never), but TypeStatement passed
                return $factory->build($statement, $this->context, $this->parser);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }
}
