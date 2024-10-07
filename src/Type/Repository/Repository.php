<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Builder\NamedTypeBuilder;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;

/**
 * @template-implements \IteratorAggregate<array-key, TypeBuilderInterface>
 */
class Repository implements RepositoryInterface, \IteratorAggregate
{
    /**
     * @var list<TypeBuilderInterface>
     */
    protected array $builders = [];

    private readonly ParserInterface $parser;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
    ) {
        $this->parser = $this->createPlatformParser($this->platform);
        $this->builders = $this->getTypeBuilders($this->platform);
    }

    /**
     * @return list<TypeBuilderInterface>
     */
    private function getTypeBuilders(PlatformInterface $platform): array
    {
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

    public function getPlatform(): PlatformInterface
    {
        return $this->platform;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     * @param class-string<TypeInterface> $type
     * @deprecated Must be removed
     */
    public function type(string $name, string $type): void
    {
        $this->builders[] = new NamedTypeBuilder($name, $type);
    }

    public function parse(string $type): TypeStatement
    {
        try {
            return $this->parser->parse($type);
        } catch (\Throwable $e) {
            throw TypeNotCreatableException::fromTypeName($type, $e);
        }
    }

    public function get(TypeStatement $type): TypeInterface
    {
        foreach ($this->builders as $factory) {
            if ($factory->isSupported($type)) {
                return $factory->build($type, $this);
            }
        }

        throw TypeNotFoundException::fromType($type);
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
