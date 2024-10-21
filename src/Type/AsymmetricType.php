<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context;

abstract class AsymmetricType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        if ($context->isDenormalization()) {
            return $this->isDenormalizable($value, $context);
        }

        return $this->isNormalizable($value, $context);
    }

    abstract protected function isNormalizable(mixed $value, Context $context): bool;

    abstract protected function isDenormalizable(mixed $value, Context $context): bool;

    public function cast(mixed $value, Context $context): mixed
    {
        if ($context->isDenormalization()) {
            return $this->denormalize($value, $context);
        }

        return $this->normalize($value, $context);
    }

    abstract protected function normalize(mixed $value, Context $context): mixed;

    abstract protected function denormalize(mixed $value, Context $context): mixed;
}
