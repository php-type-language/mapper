<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Platform\Standard\Type\BoolLiteralType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Literal\BoolLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<BoolLiteralNode, BoolLiteralType>
 */
class BoolLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof BoolLiteralNode;
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): BoolLiteralType {
        return new BoolLiteralType($statement->value);
    }
}
