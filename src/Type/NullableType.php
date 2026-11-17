<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;

/**
 * `?T` and `T|null`: {@see null} as is, any other value by the type.
 *
 * @template T
 *
 * @template-implements TypeInterface<T|null>
 */
final class NullableType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<T>
         */
        public readonly TypeInterface $type,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $value === null || $this->type->isDenormalizable($value, $context);
    }

    public function denormalize(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->type->denormalize($value, $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $value === null || $this->type->isNormalizable($value, $context);
    }

    public function normalize(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->type->normalize($value, $context);
    }
}
