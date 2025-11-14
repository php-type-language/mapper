<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Parser\Node\Stmt\TypesListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see ArrayType} from the "Type[]" syntax.
 *
 * @template-implements TypeBuilderInterface<TypesListNode<TypeStatement>, ArrayType>
 */
class TypesListBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof TypesListNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): ArrayType
    {
        $type = $context->getTypeByStatement($stmt->type);

        return new ArrayType($type);
    }
}
