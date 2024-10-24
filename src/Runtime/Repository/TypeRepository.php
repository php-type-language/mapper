<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Parser\InMemoryTypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements \IteratorAggregate<array-key, TypeBuilderInterface<TypeStatement, TypeInterface>>
 */
final class TypeRepository implements
    TypeRepositoryInterface,
    TypeParserInterface,
    \IteratorAggregate,
    \Countable
{
    /**
     * @var list<TypeBuilderInterface<TypeStatement, TypeInterface>>
     */
    protected array $builders = [];

    private readonly TypeParserInterface $parser;

    /**
     * @var \WeakMap<TypeStatement, TypeInterface>
     */
    private readonly \WeakMap $memory;

    public function __construct(
        PlatformInterface $platform = new StandardPlatform(),
        private readonly ReferencesResolver $references = new ReferencesResolver(),
    ) {
        $this->parser = new InMemoryTypeParser(
            delegate: TypeParser::createFromPlatform(
                platform: $platform,
            ),
        );
        $this->builders = $this->getTypeBuilders($platform);
        $this->memory = new \WeakMap();
    }

    /**
     * @return list<TypeBuilderInterface<TypeStatement, TypeInterface>>
     */
    private function getTypeBuilders(PlatformInterface $platform): array
    {
        /** @var iterable<array-key, TypeBuilderInterface<TypeStatement, TypeInterface>> $builders */
        $builders = $platform->getTypes();

        return match (true) {
            $builders instanceof \Traversable => \iterator_to_array($builders, false),
            \array_is_list($builders) => $builders,
            default => \array_values($builders),
        };
    }

    public function getStatementByType(#[Language('PHP')] string $type): TypeStatement
    {
        return $this->parser->getStatementByType($type);
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        return $this->parser->getStatementByValue($value);
    }

    public function getByType(string $type, ?\ReflectionClass $context = null): TypeInterface
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
        if (isset($this->memory[$statement])) {
            return $this->memory[$statement];
        }

        if ($context !== null) {
            $statement = $this->references->resolve($statement, $context);
        }

        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
                return $this->memory[$statement] = $factory->build($statement, $this);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->builders);
    }

    public function count(): int
    {
        return \count($this->builders);
    }
}
