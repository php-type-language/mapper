<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class NullableType implements LogicalTypeInterface
{
    public function __construct(
        #[TargetTemplateArgument]
        private readonly TypeInterface $parent,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NullableTypeNode(
            type: $this->parent->getTypeStatement($context),
        );
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return $value === null || (
            $this->parent instanceof LogicalTypeInterface
            && $this->parent->supportsCasting($value, $context)
        );
    }

    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->cast($value, $types, $context);
    }
}
