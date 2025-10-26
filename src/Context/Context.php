<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class Context implements
    TypeExtractorInterface,
    TypeParserInterface,
    TypeRepositoryInterface
{
    protected function __construct(
        protected readonly mixed $value,
        protected readonly Direction $direction,
        protected readonly Configuration $config,
        protected readonly TypeExtractorInterface $extractor,
        protected readonly TypeParserInterface $parser,
        protected readonly TypeRepositoryInterface $types,
    ) {}

    /**
     * Creates new child context.
     */
    public function enter(
        mixed $value,
        EntryInterface $entry,
        ?bool $strictTypes = null,
        ?bool $objectAsArray = null,
    ): self {
        return new ChildContext(
            parent: $this,
            entry: $entry,
            value: $value,
            direction: $this->direction,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
            overrideStrictTypes: $strictTypes,
            overrideObjectAsArray: $objectAsArray,
        );
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isObjectAsArray(): bool
    {
        return $this->config->isObjectAsArray();
    }

    public function isStrictTypesEnabled(): bool
    {
        return $this->config->isStrictTypesEnabled();
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
            statement: $this->getStatementByValue($value)
        );
    }
}
