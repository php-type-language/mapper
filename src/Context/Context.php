<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements \IteratorAggregate<array-key, Context>
 */
abstract class Context implements
    TypeExtractorInterface,
    TypeParserInterface,
    TypeRepositoryInterface,
    \IteratorAggregate,
    \Countable
{
    protected function __construct(
        public readonly mixed $value,
        public readonly Direction $direction,
        public readonly Configuration $config,
        public readonly TypeExtractorInterface $extractor,
        public readonly TypeParserInterface $parser,
        public readonly TypeRepositoryInterface $types,
    ) {}

    /**
     * Creates new child context.
     */
    public function enter(mixed $value, EntryInterface $entry, ?Configuration $override = null): self
    {
        return new ChildContext(
            parent: $this,
            entry: $entry,
            value: $value,
            direction: $this->direction,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
            override: $override,
        );
    }

    public function isObjectAsArray(): bool
    {
        return $this->config->isObjectAsArray();
    }

    public function isStrictTypesEnabled(): bool
    {
        return $this->config->isStrictTypesEnabled();
    }

    abstract public function getPath(): PathInterface;

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        return $this->types->getTypeByStatement($statement);
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        return $this->parser->getStatementByDefinition($definition);
    }

    /**
     * @param non-empty-string $definition
     *
     * @throws \Throwable
     */
    public function getTypeByDefinition(#[Language('PHP')] string $definition): TypeInterface
    {
        return $this->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($definition),
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
     * @throws \Throwable
     */
    public function getTypeByValue(mixed $value): TypeInterface
    {
        return $this->types->getTypeByStatement(
            statement: $this->getStatementByValue($value),
        );
    }

    /**
     * @return int<1, max>
     */
    public function count(): int
    {
        return \max(1, \iterator_count($this));
    }
}
