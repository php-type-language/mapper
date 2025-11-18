<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Common;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @phpstan-require-implements TypeParserInterface
 */
trait InteractWithTypeParser
{
    use InteractWithTypeExtractor;

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
    public readonly TypeParserInterface $parser;

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
