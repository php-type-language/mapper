<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

final class NullableType implements TypeInterface
{
    public function __construct(
        private readonly TypeInterface $parent,
    ) {}

    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->normalize($value, $types, $context);
    }

    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->denormalize($value, $types, $context);
    }
}
