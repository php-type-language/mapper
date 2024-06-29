<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Registry;

use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\PlatformInterface;
use TypeLang\Mapper\Type\Builder\NamedTypeBuilder;
use TypeLang\Mapper\Type\Builder\ObjectNamedTypeBuilder;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\InMemoryCachedParser;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

class Registry implements MutableRegistryInterface
{
    /**
     * @var list<TypeBuilderInterface>
     */
    protected array $builders = [];

    private PrinterInterface $printer;

    private readonly ParserInterface $parser;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
    ) {
        $this->printer = new PrettyPrinter();
        $this->parser = $this->createPlatformParser($this->platform);

        $this->loadPlatformTypes($this->platform);
    }

    private function loadPlatformTypes(PlatformInterface $platform): void
    {
        foreach ($platform->getBuiltinTypes() as $builder) {
            $this->append($builder);
        }
    }

    private function createPlatformParser(PlatformInterface $platform): ParserInterface
    {
        return new InMemoryCachedParser(
            parser: new Parser(
                conditional: $platform->isFeatureSupported(GrammarFeature::Conditional),
                shapes: $platform->isFeatureSupported(GrammarFeature::Shapes),
                callables: $platform->isFeatureSupported(GrammarFeature::Callables),
                literals: $platform->isFeatureSupported(GrammarFeature::Literals),
                generics: $platform->isFeatureSupported(GrammarFeature::Generics),
                union: $platform->isFeatureSupported(GrammarFeature::Union),
                intersection: $platform->isFeatureSupported(GrammarFeature::Intersection),
                list: $platform->isFeatureSupported(GrammarFeature::List),
            ),
        );
    }

    public function getPlatform(): PlatformInterface
    {
        return $this->platform;
    }

    /**
     * @api
     *
     * @return $this
     */
    public function withPrinter(PrinterInterface $printer): self
    {
        $clone = clone $this;
        $clone->printer = $printer;

        return $clone;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     * @param class-string<TypeInterface> $class
     */
    public function type(string $name, string $class): void
    {
        $this->append(new NamedTypeBuilder($name, $class));
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     * @param class-string<TypeInterface> $class
     */
    public function instanceof(string $name, string $class): void
    {
        $this->append(new ObjectNamedTypeBuilder($name, $class));
    }

    /**
     * @api
     */
    public function append(TypeBuilderInterface $type): void
    {
        $this->builders[] = $type;
    }

    /**
     * @api
     */
    public function prepend(TypeBuilderInterface $type): void
    {
        \array_unshift($this->builders, $type);
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

        throw TypeNotFoundException::fromTypeName(
            name: $this->printer->print($type),
        );
    }
}
