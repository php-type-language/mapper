<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class ArrayKeyType implements LogicalTypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'array-key';

    private readonly UnionType $delegate;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        public readonly string $name = self::DEFAULT_TYPE_NAME,
    ) {
        $this->delegate = new UnionType([
            new IntType(),
            new StringType(),
        ]);
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return $this->delegate->supportsCasting($value, $context);
    }

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return $this->delegate->getTypeStatement($context);
    }

    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): mixed
    {
        return $this->delegate->cast($value, $types, $context);
    }
}
