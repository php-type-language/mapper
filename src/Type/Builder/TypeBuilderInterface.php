<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TStmt of TypeStatement
 * @template-covariant TType of TypeInterface
 */
interface TypeBuilderInterface
{
    /**
     * @return ($statement is TStmt ? bool : false)
     */
    public function isSupported(TypeStatement $statement): bool;

    /**
     * @param TStmt $statement
     *
     * @return TType
     * @throws DefinitionException in case of building error
     * @throws \Throwable in case of any internal error
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): TypeInterface;
}
