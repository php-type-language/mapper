<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\Context\LocalContext;

class MixedType extends SimpleType
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_TYPE_NAME = 'mixed';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name = self::DEFAULT_TYPE_NAME,
    ) {
        parent::__construct($name);
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return true;
    }

    /**
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, LocalContext $context): mixed
    {
        return $context->getTypes()
            ->getByValue($value)
            ->cast($value, $context);
    }
}
