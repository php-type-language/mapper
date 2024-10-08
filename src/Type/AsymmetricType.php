<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Context\LocalContext;

abstract class AsymmetricType implements TypeInterface
{
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
