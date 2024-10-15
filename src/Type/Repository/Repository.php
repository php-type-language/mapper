<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Repository\Reference\NativeReferencesReader;
use TypeLang\Mapper\Type\Repository\Reference\ReferencesReaderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;
use TypeLang\Parser\Traverser;
use TypeLang\Parser\TypeResolver;

/**
 * @template-implements \IteratorAggregate<array-key, TypeBuilderInterface<TypeStatement, TypeInterface>>
 */
class Repository implements RepositoryInterface, \IteratorAggregate
{
    /**
     * @var list<TypeBuilderInterface<TypeStatement, TypeInterface>>
     */
    protected array $builders = [];

    private readonly ParserInterface $parser;

    private readonly TypeResolver $typeResolver;

    public function __construct(
        PlatformInterface $platform = new StandardPlatform(),
        private readonly ReferencesReaderInterface $references = new NativeReferencesReader(),
    ) {
        $this->typeResolver = new TypeResolver();
        $this->parser = $this->createPlatformParser($platform);
        $this->builders = $this->getTypeBuilders($platform);
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

    private function createPlatformParser(PlatformInterface $platform): ParserInterface
    {
        return new Parser(
            conditional: $platform->isFeatureSupported(GrammarFeature::Conditional),
            shapes: $platform->isFeatureSupported(GrammarFeature::Shapes),
            callables: $platform->isFeatureSupported(GrammarFeature::Callables),
            literals: $platform->isFeatureSupported(GrammarFeature::Literals),
            generics: $platform->isFeatureSupported(GrammarFeature::Generics),
            union: $platform->isFeatureSupported(GrammarFeature::Union),
            intersection: $platform->isFeatureSupported(GrammarFeature::Intersection),
            list: $platform->isFeatureSupported(GrammarFeature::List),
            hints: $platform->isFeatureSupported(GrammarFeature::Hints),
            attributes: $platform->isFeatureSupported(GrammarFeature::Attributes),
        );
    }

    public function parse(string $type): TypeStatement
    {
        return $this->parser->parse($type);
    }

    public function getByType(string $type, ?\ReflectionClass $class = null): TypeInterface
    {
        $statement = $this->parse($type);

        return $this->getByStatement($statement, $class);
    }

    public function getByValue(mixed $value, ?\ReflectionClass $class = null): TypeInterface
    {
        // @phpstan-ignore-next-line : False-positive, the 'get_debug_type' method returns a non-empty string
        $statement = new NamedTypeNode(\get_debug_type($value));

        return $this->getByStatement($statement);
    }

    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $class = null): TypeInterface
    {
        if ($class !== null) {
            // Performs Name conversions if the required type is found
            // in the same namespace as the declared dependency.
            $statement = $this->resolveFromNamespace($statement, $class);

            $uses = $this->references->getUseStatements($class);

            // Additionally performs Name conversions if the required
            // type was specified in "use" statement.
            $statement = $this->typeResolver->resolveWith($statement, $uses);
        }

        foreach ($this->builders as $factory) {
            if ($factory->isSupported($statement)) {
                return $factory->build($statement, $this);
            }
        }

        throw TypeNotFoundException::becauseTypeNotDefined($statement);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function resolveFromNamespace(TypeStatement $statement, \ReflectionClass $class): TypeStatement
    {
        return $this->typeResolver->resolve($statement, static function (Name $name) use ($class): ?Name {
                $namespace = $class->getNamespaceName();

                if ($namespace !== '' && self::entryExists($namespace . '\\' . $name->toString())) {
                    return (new Name($namespace))->withAdded($name);
                }

                return null;
            },
        );
    }

    private static function entryExists(string $fqn): bool
    {
        return \class_exists($fqn)
            || \interface_exists($fqn, false);
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
