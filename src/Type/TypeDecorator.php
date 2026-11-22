<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;

/**
 * Base of a type that wraps another one: every operation is passed to the
 * wrapped type, a subclass overrides the ones it changes.
 *
 * @template T
 *
 * @template-implements TypeInterface<T>
 */
abstract class TypeDecorator implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<T>
         */
        protected readonly TypeInterface $delegate,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $this->delegate->isDenormalizable($value, $context);
    }

    public function denormalize(mixed $value, Context $context): mixed
    {
        return $this->delegate->denormalize($value, $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $this->delegate->isNormalizable($value, $context);
    }

    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->delegate->normalize($value, $context);
    }
}
