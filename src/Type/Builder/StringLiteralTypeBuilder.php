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
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof StringLiteralNode;
    }

    public function build(TypeStatement $statement, BuildingContext $context): StringLiteralType
    {
        return new StringLiteralType($statement->value);
    }
}
