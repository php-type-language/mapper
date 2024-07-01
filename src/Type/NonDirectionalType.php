<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

abstract class NonDirectionalType implements TypeInterface
{
    abstract protected function format(mixed $value, RegistryInterface $types, LocalContext $context): mixed;

    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        return $this->format($value, $types, $context);
    }

    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        return $this->format($value, $types, $context);
    }
}
