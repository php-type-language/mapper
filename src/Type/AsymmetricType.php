<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Context\LocalContext;

abstract class AsymmetricType implements TypeInterface
{
    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        if ($context->isDenormalization()) {
            return $this->supportsDenormalization($value, $context);
        }

        return $this->supportsNormalization($value, $context);
    }

    abstract protected function supportsNormalization(mixed $value, LocalContext $context): bool;

    abstract protected function supportsDenormalization(mixed $value, LocalContext $context): bool;

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
