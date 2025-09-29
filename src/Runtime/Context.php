<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Context\ChildContext;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class Context implements
    ConfigurationInterface,
    TypeExtractorInterface,
    TypeParserInterface,
    TypeRepositoryInterface
{
    protected function __construct(
        protected readonly mixed $value,
        protected readonly Direction $direction,
        protected readonly TypeExtractorInterface $extractor,
        protected readonly TypeParserInterface $parser,
        protected readonly TypeRepositoryInterface $types,
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
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
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

    /**
     * @api
     */
    public function isNormalization(): bool
    {
        return $this->direction === Direction::Normalize;
    }

    /**
     * @api
     */
    public function isDenormalization(): bool
    {
        return $this->direction === Direction::Denormalize;
    }

    abstract public function getPath(): PathInterface;

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getTypeByStatement($statement, $context);
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        return $this->parser->getStatementByDefinition($definition);
    }

    /**
     * @param non-empty-string $definition
     * @param \ReflectionClass<object>|null $context
     *
     * @throws \Throwable
     */
    public function getTypeByDefinition(#[Language('PHP')] string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($definition),
            context: $context,
        );
    }

    public function getDefinitionByValue(mixed $value): string
    {
        return $this->extractor->getDefinitionByValue($value);
    }

    /**
     * @throws \Throwable
     */
    public function getStatementByValue(mixed $value): TypeStatement
    {
        return $this->parser->getStatementByDefinition(
            definition: $this->getDefinitionByValue($value),
        );
    }

    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws \Throwable
     */
    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getTypeByStatement(
            statement: $this->getStatementByValue($value),
            context: $context,
        );
    }
}
