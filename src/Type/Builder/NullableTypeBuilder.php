<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
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

    /**
     * @throws TypeNotFoundException
     */
    public function build(TypeStatement $type, RepositoryInterface $context): NullableType
    {
        assert($type instanceof NullableTypeNode);

        return new NullableType($context->getByStatement($type->type));
    }
}
