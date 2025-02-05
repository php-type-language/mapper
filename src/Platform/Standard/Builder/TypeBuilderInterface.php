<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
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
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface;
}
