<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Responsible for creating types based on their declaration
 *
 * @template TStmt of TypeStatement = TypeStatement
 * @template-covariant TType of TypeInterface = TypeInterface
 */
interface TypeBuilderInterface
{
    /**
     * Returns {@see true} if the specified builder can create an instance
     * of the specified type by the given {@see TypeStatement}
     *
     * @phpstan-assert-if-true TStmt $stmt
     */
    public function isSupported(TypeStatement $stmt): bool;

    /**
     * Creates an instance of the desired type
     *
     * @param TStmt $stmt
     *
     * @return TType
     * @throws DefinitionException in case of building error
     * @throws \Throwable in case of any internal error
     */
    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface;
}
