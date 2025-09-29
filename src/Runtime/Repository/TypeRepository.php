<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\Reference\Reader\NativeReferencesReader;
use TypeLang\Mapper\Runtime\Repository\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\Runtime\Repository\Reference\ReferencesResolver;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeRepository implements
    TypeRepositoryInterface,
    TypeRepositoryDecoratorInterface
{
    /**
     * @var list<TypeBuilderInterface<covariant TypeStatement, TypeInterface>>
     */
    private array $builders = [];

    private TypeRepositoryInterface $context;

    private readonly ReferencesResolver $references;

    public function __construct(
        private readonly TypeParserInterface $parser,
        private readonly PlatformInterface $platform,
        ReferencesReaderInterface $references = new NativeReferencesReader(),
    ) {
        $this->context = $this;
        $this->references = new ReferencesResolver($references);
        $this->builders = self::toArrayList($this->platform->getTypes());
    }

    /**
     * @param iterable<array-key, TypeBuilderInterface<covariant TypeStatement, TypeInterface>> $types
     *
     * @return list<TypeBuilderInterface<covariant TypeStatement, TypeInterface>>
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

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        if ($context !== null) {
            $statement = $this->references->resolve($statement, $context);
        }

        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                // @phpstan-ignore-next-line : Statement expects a bottom type (never), but TypeStatement passed
                return $factory->build($statement, $this->context, $this->parser);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }
}
