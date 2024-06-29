<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Registry\RegistryInterface;

/**
 * @template TInput of mixed
 * @template TOutput of mixed
 *
 * @template-extends NonDirectionalType<TInput|null, TOutput|null>
 */
final class NullableType extends NonDirectionalType
{
    public function __construct(
        private readonly TypeInterface $parent,
    ) {}

    protected function format(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->normalize($value, $types, $context);
    }
}
