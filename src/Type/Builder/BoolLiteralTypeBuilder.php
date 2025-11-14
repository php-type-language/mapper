<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\BoolLiteralType;
use TypeLang\Parser\Node\Literal\BoolLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<BoolLiteralNode, BoolLiteralType>
 */
class BoolLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof BoolLiteralNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): BoolLiteralType
    {
        return new BoolLiteralType($stmt->value);
    }
}
