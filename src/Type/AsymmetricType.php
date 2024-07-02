<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

abstract class AsymmetricType implements TypeInterface
{
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if ($context->isDenormalization()) {
            return $this->denormalize($value, $types, $context);
        }

        return $this->normalize($value, $types, $context);
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        if ($context->isDenormalization()) {
            return $this->supportsDenormalization($value, $context);
        }

        return $this->supportsNormalization($value, $context);
    }

    abstract protected function supportsNormalization(mixed $value, LocalContext $context): bool;

    abstract protected function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed;

    abstract protected function supportsDenormalization(mixed $value, LocalContext $context): bool;

    abstract protected function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed;
}
