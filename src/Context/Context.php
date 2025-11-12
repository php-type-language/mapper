<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class Context implements
    TypeExtractorInterface,
    TypeParserInterface,
    TypeRepositoryInterface
{
    public function __construct(
        /**
         * Gets current configuration.
         *
         * If you need to retrieve configuration's settings, it is recommended
         * to use the following methods:
         *
         * - {@see MappingContext::isObjectAsArray()}
         * - {@see MappingContext::isStrictTypesEnabled()}
         */
        public readonly Configuration $config,
        /**
         * Responsible for obtaining the type declaration from its value.
         *
         * This extractor belongs to the current context and may differ from the
         * initial (mappers) one.
         *
         * You can safely use all the methods of this interface, but for ease of
         * use, the following methods are available to you:
         *
         * - {@see MappingContext::getDefinitionByValue()} - returns definition string
         *   by the passed value.
         */
        public readonly TypeExtractorInterface $extractor,
        /**
         * Responsible for obtaining the type AST (Abstract Syntax Tree)
         * statements by the definition.
         *
         * This parser belongs to the current context and may differ from the
         * initial (mappers) one.
         *
         * You can safely use all the methods of this interface, but for ease of
         * use, the following methods are available to you:
         *
         * - {@see MappingContext::getStatementByValue()} - returns statement node by
         *   the value.
         * - {@see MappingContext::getStatementByDefinition()} - returns statement node
         *   by the definition string.
         */
        public readonly TypeParserInterface $parser,
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
         * - {@see MappingContext::getTypeByValue()} - returns type instance by the
         *   passed value.
         * - {@see MappingContext::getTypeByDefinition()} - returns type instance by
         *   the type definition string.
         * - {@see MappingContext::getTypeByStatement()} - returns type instance by
         *   the type statement.
         */
        public readonly TypeRepositoryInterface $types,
    ) {}

    /**
     * A more convenient and correct way to get current "object as array"
     * configuration value.
     *
     * @see Configuration::isObjectAsArray()
     *
     * @link https://en.wikipedia.org/wiki/Law_of_Demeter
     */
    public function isObjectAsArray(): bool
    {
        return $this->config->isObjectAsArray();
    }

    /**
     * A more convenient and correct way to get current "strict types"
     * configuration value.
     *
     * @see Configuration::isStrictTypesEnabled()
     *
     * @link https://en.wikipedia.org/wiki/Law_of_Demeter
     */
    public function isStrictTypesEnabled(): bool
    {
        return $this->config->isStrictTypesEnabled();
    }

    public function getDefinitionByValue(mixed $value): string
    {
        return $this->extractor->getDefinitionByValue($value);
    }

    /**
     * Returns an AST statement describing the type by the value.
     *
     * ```
     * $statement = $context->getStatementByValue(42);
     *
     * // TypeLang\Parser\Node\Stmt\NamedTypeNode {
     * //     +name: TypeLang\Parser\Node\Name {
     * //         +parts: array:1 [
     * //             TypeLang\Parser\Node\Identifier { +value: "int" }
     * //         ]
     * //     }
     * //     +arguments: null
     * //     +fields: null
     * // }
     * ```
     *
     * @throws \Throwable
     */
    public function getStatementByValue(mixed $value): TypeStatement
    {
        return $this->parser->getStatementByDefinition(
            definition: $this->extractor->getDefinitionByValue(
                value: $value,
            ),
        );
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        return $this->parser->getStatementByDefinition(
            definition: $definition,
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
}
