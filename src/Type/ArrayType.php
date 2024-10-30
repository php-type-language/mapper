<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;

class ArrayType implements TypeInterface
{
    protected readonly TypeInterface $key;
    protected readonly bool $isKeyPassed;

    protected readonly TypeInterface $value;
    protected readonly bool $isValuePassed;

    public function __construct(
        ?TypeInterface $key = null,
        ?TypeInterface $value = null,
    ) {
        $this->key = $key ?? new ArrayKeyType();
        $this->isKeyPassed = $key !== null;

        $this->value = $value ?? new MixedType();
        $this->isValuePassed = $value !== null;
    }

    public function match(mixed $value, Context $context): bool
    {
        if (!\is_iterable($value)) {
            return false;
        }

        // Force return true if the iterator does not allow rewinding.
        if ($value instanceof \Generator) {
            return true;
        }

        foreach ($value as $key => $item) {
            $entrance = $context->enter($item, new ArrayIndexEntry($key));

            $isValidItem = $this->key->match($key, $entrance)
                && $this->value->match($value, $entrance);

            if (!$isValidItem) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     * @throws \Throwable
     * @throws RuntimeExceptionInterface
     */
    public function cast(mixed $value, Context $context): array
    {
        if (!\is_iterable($value)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        $result = [];

        foreach ($value as $index => $item) {
            $entrance = $context->enter($item, new ArrayIndexEntry($index));

            $result[$this->key->cast($index, $entrance)]
                = $this->value->cast($item, $entrance);
        }

        return $result;
    }
}
