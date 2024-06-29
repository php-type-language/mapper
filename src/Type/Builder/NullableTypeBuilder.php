<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see NullableType} from "?Type" syntax.
 */
final class NullableTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NullableTypeNode;
    }

    public function build(TypeStatement $type, RegistryInterface $context): TypeInterface
    {
        assert($type instanceof NullableTypeNode);

        return new NullableType(
            parent: $context->get($type->type),
        );
    }
}
