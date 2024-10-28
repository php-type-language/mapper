<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeRepository implements
    TypeRepositoryInterface,
    InnerTypeRepositoryContainerInterface
{
    /**
     * @var list<TypeBuilderInterface<covariant TypeStatement, TypeInterface>>
     */
    private array $builders = [];

    private TypeRepositoryInterface $inner;

    /**
     * @param iterable<array-key, TypeBuilderInterface<covariant TypeStatement, TypeInterface>> $types
     */
    public function __construct(
        private readonly TypeParserInterface $parser,
        iterable $types = [],
        private readonly ReferencesResolver $references = new ReferencesResolver(),
    ) {
        $this->inner = $this;
        $this->builders = match (true) {
            $types instanceof \Traversable => \iterator_to_array($types, false),
            \array_is_list($types) => $types,
            default => \array_values($types),
        };
    }

    public function setInnerContext(TypeRepositoryInterface $inner): void
    {
        $this->inner = $inner;
    }

    /**
     * TODO should me moved to an external factory class
     */
    public static function createFromPlatform(
        PlatformInterface $platform,
        TypeParserInterface $parser,
        ReferencesResolver $references = new ReferencesResolver(),
    ): self {
        return new self(
            parser: $parser,
            types: $platform->getTypes(),
            references: $references,
        );
    }

    public function getTypeByDefinition(#[Language('PHP')] string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parser->getStatementByDefinition($definition);

        return $this->inner->getTypeByStatement($statement, $context);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parser->getStatementByValue($value);

        return $this->inner->getTypeByStatement($statement, $context);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        if ($context !== null) {
            $statement = $this->references->resolve($statement, $context);
        }

        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                // @phpstan-ignore-next-line : Statement expects a bottom type (never), but TypeStatement passed
                return $factory->build($statement, $this->inner, $this->parser);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }
}
