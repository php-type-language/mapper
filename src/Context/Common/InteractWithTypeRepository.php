<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Common;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @phpstan-require-implements TypeRepositoryInterface
 */
trait InteractWithTypeRepository
{
    use InteractWithTypeParser;

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
    public readonly TypeRepositoryInterface $types;

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
