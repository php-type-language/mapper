<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context\ChildContext;
use TypeLang\Mapper\Runtime\Context\DirectionInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserFacadeInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Path\PathProviderInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryFacadeInterface;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class Context implements
    TypeRepositoryFacadeInterface,
    ConfigurationInterface,
    PathProviderInterface,
    TypeParserFacadeInterface,
    DirectionInterface
{
    protected function __construct(
        protected readonly mixed $value,
        protected readonly DirectionInterface $direction,
        protected readonly TypeRepositoryFacadeInterface $types,
        protected readonly TypeParserFacadeInterface $parser,
        protected readonly ConfigurationInterface $config,
    ) {}

    /**
     * Creates new child context.
     */
    public function enter(mixed $value, EntryInterface $entry): self
    {
        return new ChildContext(
            parent: $this,
            entry: $entry,
            value: $value,
            direction: $this->direction,
            types: $this->types,
            parser: $this->parser,
            config: $this->config,
        );
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isObjectsAsArrays(): bool
    {
        return $this->config->isObjectsAsArrays();
    }

    public function isDetailedTypes(): bool
    {
        return $this->config->isDetailedTypes();
    }

    public function isStrictTypesEnabled(): bool
    {
        return $this->config->isStrictTypesEnabled();
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->config->getLogger();
    }

    public function getTracer(): ?TracerInterface
    {
        return $this->config->getTracer();
    }

    public function isNormalization(): bool
    {
        return $this->direction->isNormalization();
    }

    public function isDenormalization(): bool
    {
        return $this->direction->isDenormalization();
    }

    public function getPath(): PathInterface
    {
        return new Path();
    }

    public function getTypeByDefinition(string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getTypeByDefinition($definition, $context);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getTypeByValue($value, $context);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getTypeByStatement($statement, $context);
    }

    public function getStatementByDefinition(string $definition): TypeStatement
    {
        return $this->parser->getStatementByDefinition($definition);
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        return $this->parser->getStatementByValue($value);
    }
}
