<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Context\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Context\Path\Entry\UnionLeafEntry;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements \IteratorAggregate<array-key, RuntimeContext>
 */
abstract class RuntimeContext extends MapperContext implements
    TypeRepositoryInterface,
    \IteratorAggregate,
    \Countable
{
    protected function __construct(
        /**
         * Gets original (unmodified) value.
         *
         * Please note that the value may be changed during type manipulation
         * (casting), for example, using {@see TypeCoercerInterface}.
         *
         * In this case, the `$value` in the {@see RuntimeContext} remains the original
         * value, without any mutations from type coercions.
         */
        public readonly mixed $value,
        /**
         * Gets data transformation direction.
         */
        public readonly DirectionInterface $direction,
        /**
         * Responsible for obtaining the type ({@see TypeInterface}) instances
         * by the type statement.
         *
         * This repository belongs to the current context and may differ from
         * the initial (mappers) one.
         *
         * You can safely use all the methods of this interface, but for ease of
         * use, the following methods are available to you:
         *
         * - {@see RuntimeContext::getTypeByValue()} - returns type instance by the
         *   passed value.
         * - {@see RuntimeContext::getTypeByDefinition()} - returns type instance by
         *   the type definition string.
         * - {@see RuntimeContext::getTypeByStatement()} - returns type instance by
         *   the type statement.
         */
        public readonly TypeRepositoryInterface $types,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        Configuration $config,
        /**
         * Contains a reference to the original config created during this
         * context initialization.
         *
         * If there is no reference ({@see null}), then the current config in
         * the {@see $config} context's field is the original.
         */
        public readonly ?Configuration $original = null,
    ) {
        parent::__construct(
            extractor: $extractor,
            parser: $parser,
            config: $config,
        );
    }

    /**
     * Returns the {@see TypeInterface} instance associated with passed value.
     *
     * This method can be used, for example, when implementing a {@see mixed}
     * type, where the type receives an arbitrary value that should be
     * associated with a specific type.
     *
     * @throws \Throwable
     */
    public function getTypeByValue(mixed $value): TypeInterface
    {
        return $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition(
                definition: $this->extractor->getDefinitionByValue(
                    value: $value,
                ),
            ),
        );
    }

    /**
     * Returns the {@see TypeInterface} instance by the type definition string.
     *
     * @param non-empty-string $definition
     *
     * @throws \Throwable
     */
    public function getTypeByDefinition(#[Language('PHP')] string $definition): TypeInterface
    {
        return $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition(
                definition: $definition,
            ),
        );
    }

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        return $this->types->getTypeByStatement(
            statement: $statement,
        );
    }

    /**
     * Creates new child context.
     */
    public function enter(mixed $value, EntryInterface $entry, ?Configuration $config = null): self
    {
        // Original configuration
        $original = $this->original ?? $this->config;

        // Configuration of the current context
        $current = $config ?? $original;

        // Do not set "previous" config in case of
        // "current" config is original
        if ($current === $original) {
            $original = null;
        }

        return new ChildRuntimeContext(
            parent: $this,
            entry: $entry,
            value: $value,
            direction: $this->direction,
            types: $this->types,
            extractor: $this->extractor,
            parser: $this->parser,
            config: $current,
            original: $current === $original ? null : $original,
        );
    }

    /**
     * Creates an "array index" child context
     *
     * @api
     *
     * @param array-key $index
     */
    public function enterIntoArrayIndex(mixed $value, int|string $index, ?Configuration $config = null): self
    {
        return $this->enter($value, new ArrayIndexEntry($index), $config);
    }

    /**
     * Creates an "object" child context
     *
     * @api
     *
     * @param class-string $class
     */
    public function enterIntoObject(mixed $value, string $class, ?Configuration $config = null): self
    {
        return $this->enter($value, new ObjectEntry($class), $config);
    }

    /**
     * Creates an "object's property" child context
     *
     * @api
     *
     * @param non-empty-string $name
     */
    public function enterIntoObjectProperty(mixed $value, string $name, ?Configuration $override = null): self
    {
        return $this->enter($value, new ObjectPropertyEntry($name), $override);
    }

    /**
     * Creates an "union leaf" child context
     *
     * @api
     *
     * @param int<0, max> $index
     */
    public function enterIntoUnionLeaf(mixed $value, int $index, ?Configuration $override = null): self
    {
        return $this->enter($value, new UnionLeafEntry($index), $override);
    }

    /**
     * Sets the value of the "object as array" configuration settings using
     * the original configuration rules.
     *
     * Note that the {@see $config} property contains the **current** context
     * configuration settings, which may differ from the original ones.
     * Therefore, method {@see RuntimeContext::withObjectAsArray()} is not equivalent
     * to calling {@see Configuration::withObjectAsArray()}.
     */
    public function withObjectAsArray(?bool $enabled): Configuration
    {
        if ($enabled === null) {
            return $this->original ?? $this->config;
        }

        return ($this->original ?? $this->config)
            ->withObjectAsArray($enabled);
    }

    /**
     * Sets the value of the "strict types" configuration settings using
     * the original configuration rules.
     *
     * Note that the {@see $config} property contains the **current** context
     * configuration settings, which may differ from the original ones.
     * Therefore, method {@see RuntimeContext::withStrictTypes()} is not equivalent
     * to calling {@see Configuration::withStrictTypes()}.
     */
    public function withStrictTypes(?bool $enabled): Configuration
    {
        if ($enabled === null) {
            return $this->original ?? $this->config;
        }

        return ($this->original ?? $this->config)
            ->withStrictTypes($enabled);
    }

    /**
     * Returns current context path.
     *
     * The {@see PathInterface} contains all occurrences used in "parent"
     * (composite) types when calling {@see enter()} method.
     */
    abstract public function getPath(): PathInterface;

    /**
     * @return int<1, max>
     */
    public function count(): int
    {
        return \max(1, \iterator_count($this));
    }
}
