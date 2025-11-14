<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\FloatLiteralType;
use TypeLang\Parser\Node\Literal\FloatLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<FloatLiteralNode, FloatLiteralType>
 */
class FloatLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof FloatLiteralNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): FloatLiteralType
    {
        return new FloatLiteralType($stmt->value);
    }
}
