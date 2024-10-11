<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context\LocalContext;

abstract class AsymmetricType implements TypeInterface
{
    public function match(mixed $value, LocalContext $context): bool
    {
        if ($context->isDenormalization()) {
            return $this->isDenormalizable($value, $context);
        }

        return $this->isNormalizable($value, $context);
    }

    abstract protected function isNormalizable(mixed $value, LocalContext $context): bool;

    abstract protected function isDenormalizable(mixed $value, LocalContext $context): bool;

    public function cast(mixed $value, LocalContext $context): mixed
    {
        if ($context->isDenormalization()) {
            return $this->denormalize($value, $context);
        }

        return $this->normalize($value, $context);
    }

    abstract protected function normalize(mixed $value, LocalContext $context): mixed;

    abstract protected function denormalize(mixed $value, LocalContext $context): mixed;
}
