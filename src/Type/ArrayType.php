<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 * @template-extends IterableToArrayType<TKey, TValue>
 */
class ArrayType extends IterableToArrayType
{
    /**
     * @phpstan-assert-if-true array<array-key, mixed> $value
     */
    #[\Override]
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return \is_array($value);
    }

    #[\Override]
    public function cast(mixed $value, RuntimeContext $context): array
    {
        if (!\is_array($value)) {
            throw InvalidValueException::createFromContext($context);
        }

        return $this->process($value, $context);
    }
}
