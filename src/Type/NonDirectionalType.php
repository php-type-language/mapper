<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

/**
 * @template TValue of mixed
 * @template-implements TypeInterface<TValue, TValue>
 */
abstract class NonDirectionalType implements TypeInterface
{
    /**
     * @param TValue|mixed $value
     *
     * @return TValue
     */
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
