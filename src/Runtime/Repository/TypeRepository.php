<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Repository\Reference\NativeReferencesReader;
use TypeLang\Mapper\Runtime\Repository\Reference\ReferencesReaderInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;
use TypeLang\Parser\TypeResolver;

/**
 * @template-implements \IteratorAggregate<array-key, TypeBuilderInterface<TypeStatement, TypeInterface>>
 */
final class TypeRepository implements \IteratorAggregate, \Countable
{
    /**
     * @var list<TypeBuilderInterface<TypeStatement, TypeInterface>>
     */
    protected array $builders = [];

    private readonly ParserInterface $parser;

    private readonly TypeResolver $typeResolver;

    /**
     * @var \WeakMap<TypeStatement, TypeInterface>
     */
    private readonly \WeakMap $memory;

    public function __construct(
        PlatformInterface $platform = new StandardPlatform(),
        private readonly ReferencesReaderInterface $references = new NativeReferencesReader(),
    ) {
        $this->typeResolver = new TypeResolver();
        $this->parser = $this->createPlatformParser($platform);
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

    private function createPlatformParser(PlatformInterface $platform): ParserInterface
    {
        return new InMemoryCachedParser(new Parser(
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
        ));
    }

    /**
     * @param non-empty-string $type
     *
     * @throws ParserExceptionInterface
     * @throws \Throwable
     */
    public function parse(string $type): TypeStatement
    {
        return $this->parser->parse($type);
    }

    /**
     * @param non-empty-string $type
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable
     */
    public function getByType(string $type, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parse($type);

        // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
        return $this->memory[$statement]
            ??= $this->getByStatement($statement, $context);
    }

    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable
     */
    public function getByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        // @phpstan-ignore-next-line : False-positive, the 'get_debug_type' method returns a non-empty string
        $statement = new NamedTypeNode(\get_debug_type($value));

        return $this->getByStatement($statement, $context);
    }

    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable
     */
    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        if ($context !== null) {
            // Performs Name conversions if the required type is found
            // in the same namespace as the declared dependency.
            $statement = $this->resolveFromNamespace($statement, $context);

            $uses = $this->references->getUseStatements($context);

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
        return $this->typeResolver->resolve(
            $statement,
            static function (Name $name) use ($class): ?Name {
                $namespace = $class->getNamespaceName();

                // Replace "namespace\ClassName" sequences to current
                // namespace of the class.
                if (!$name->isSimple()) {
                    $first = $name->getFirstPart();

                    if ($first->toLowerString() === 'namespace') {
                        // Return name AS IS in case of namespace is global
                        if ($namespace === '') {
                            return $name->slice(1);
                        }

                        return (new Name($namespace))
                            ->withAdded($name->slice(1));
                    }
                }

                if ($namespace !== '' && self::entryExists($namespace . '\\' . $name->toString())) {
                    return (new Name($namespace))
                        ->withAdded($name);
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
