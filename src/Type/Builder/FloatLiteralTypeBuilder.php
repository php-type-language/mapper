<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\FloatLiteralType;
use TypeLang\Parser\Node\Literal\FloatLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<FloatLiteralNode, FloatLiteralType>
 */
class FloatLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof FloatLiteralNode;
    }

    public function build(TypeStatement $statement, TypeRepository $types): FloatLiteralType
    {
        return new FloatLiteralType($statement->value);
    }
}
