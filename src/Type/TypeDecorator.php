<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;

abstract class TypeDecorator implements TypeInterface
{
    public function __construct(
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
