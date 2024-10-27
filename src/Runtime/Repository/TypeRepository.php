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

final class TypeRepository implements TypeRepositoryInterface
{
    /**
     * @var list<TypeBuilderInterface<covariant TypeStatement, TypeInterface>>
     */
    protected array $builders = [];

    /**
     * @param iterable<array-key, TypeBuilderInterface<covariant TypeStatement, TypeInterface>> $types
     */
    public function __construct(
        private readonly TypeParserInterface $parser,
        iterable $types = [],
        private readonly ReferencesResolver $references = new ReferencesResolver(),
    ) {
        $this->builders = match (true) {
            $types instanceof \Traversable => \iterator_to_array($types, false),
            \array_is_list($types) => $types,
            default => \array_values($types),
        };
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

    public function getByType(#[Language('PHP')] string $type, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parser->getStatementByType($type);

        return $this->getByStatement($statement, $context);
    }

    public function getByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parser->getStatementByValue($value);

        return $this->getByStatement($statement, $context);
    }

    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        if ($context !== null) {
            $statement = $this->references->resolve($statement, $context);
        }

        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                // @phpstan-ignore-next-line : Statement expects a bottom type (never), but TypeStatement passed
                return $factory->build($statement, $this, $this->parser);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }
}
