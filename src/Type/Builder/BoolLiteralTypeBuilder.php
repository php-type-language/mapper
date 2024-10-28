<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;
use TypeLang\Mapper\Type\BoolLiteralType;
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
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): BoolLiteralType {
        return new BoolLiteralType($statement->value);
    }
}
