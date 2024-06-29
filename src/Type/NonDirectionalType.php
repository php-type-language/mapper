<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type;

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Registry\RegistryInterface;

/**
 * @template TInput of mixed
 * @template TOutput of mixed
 *
 * @template-implements TypeInterface<TInput, TOutput, TInput, TOutput>
 */
abstract class NonDirectionalType implements TypeInterface
{
    /**
     * @param TInput|mixed $value
     * @return TOutput
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
