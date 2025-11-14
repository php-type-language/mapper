<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see NullableType} from "?Type" syntax.
 *
 * @template-implements TypeBuilderInterface<NullableTypeNode<TypeStatement>, NullableType>
 */
class NullableTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof NullableTypeNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): NullableType
    {
        $type = $context->getTypeByStatement($stmt->type);

        return new NullableType($type);
    }
}
