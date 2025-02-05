<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Platform\Standard\Type\IntLiteralType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<IntLiteralNode, IntLiteralType>
 */
class IntLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof IntLiteralNode;
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): IntLiteralType {
        return new IntLiteralType($statement->value);
    }
}
