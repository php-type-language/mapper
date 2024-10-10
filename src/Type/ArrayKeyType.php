<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Context\LocalContext;

class ArrayKeyType extends SimpleType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'array-key';

    private readonly UnionType $delegate;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = self::DEFAULT_TYPE_NAME)
    {
        parent::__construct($name);

        $this->delegate = new UnionType([new IntType(), new StringType()]);
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return $this->delegate->match($value, $context);
    }

    public function cast(mixed $value, LocalContext $context): mixed
    {
        return $this->delegate->cast($value, $context);
    }
}
