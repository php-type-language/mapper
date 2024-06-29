<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type;

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Registry\RegistryInterface;

/**
 * @template TInput of mixed
 * @template TOutput of mixed
 */
interface TypeInterface
{
    /**
     * @param TInput|mixed $value
     *
     * @return TOutput
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed;

    /**
     * @param TOutput|mixed $value
     *
     * @return TInput
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed;
}
