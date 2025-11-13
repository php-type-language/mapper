<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MapperContext implements
    TypeExtractorInterface,
    TypeParserInterface
{
    protected function __construct(
        /**
         * Responsible for obtaining the type declaration from its value.
         *
         * This extractor belongs to the current context and may differ from the
         * initial (mappers) one.
         *
         * You can safely use all the methods of this interface, but for ease of
         * use, the following methods are available to you:
         *
         * - {@see RuntimeContext::getDefinitionByValue()} - returns definition string
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
         * - {@see RuntimeContext::getStatementByValue()} - returns statement node by
         *   the value.
         * - {@see RuntimeContext::getStatementByDefinition()} - returns statement node
         *   by the definition string.
         */
        public readonly TypeParserInterface $parser,
        /**
         * Gets current configuration.
         *
         * If you need to retrieve configuration's settings, it is recommended
         * to use the following methods:
         *
         * - {@see RuntimeContext::isObjectAsArray()}
         * - {@see RuntimeContext::isStrictTypesEnabled()}
         */
        public readonly Configuration $config,
    ) {}

    public static function create(
        Configuration $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
    ): self {
        return new self(
            extractor: $extractor,
            parser: $parser,
            config: $config,
        );
    }

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
}
