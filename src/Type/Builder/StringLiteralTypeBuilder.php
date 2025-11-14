<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\StringLiteralType;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<StringLiteralNode, StringLiteralType>
 */
class StringLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof StringLiteralNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): StringLiteralType
    {
        return new StringLiteralType($stmt->value);
    }
}
