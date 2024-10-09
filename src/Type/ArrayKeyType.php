<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class ArrayKeyType implements TypeInterface
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

    public function match(mixed $value, LocalContext $context): bool
    {
        return $this->delegate->match($value, $context);
    }

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return $this->delegate->getTypeStatement($context);
    }

    public function cast(mixed $value, LocalContext $context): mixed
    {
        return $this->delegate->cast($value, $context);
    }
}
