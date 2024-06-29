<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see NullableType} from "?Type" syntax.
 *
 * @template TInput of mixed
 * @template TOutput of mixed
 */
final class NullableTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NullableTypeNode;
    }

    /**
     * @return NullableType<TInput, TOutput>
     * @throws TypeNotFoundException
     */
    public function build(TypeStatement $type, RegistryInterface $context): NullableType
    {
        assert($type instanceof NullableTypeNode);

        /** @var NullableType<TInput, TOutput> */
        return new NullableType($context->get($type->type));
    }
}
