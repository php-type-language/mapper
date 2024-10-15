<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context\LocalContext;
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

    public function match(mixed $value, LocalContext $context): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            $context->enter(new ArrayIndexEntry($key));

            $isValidItem = $this->key->match($key, $context)
                && $this->value->match($value, $context);

            if (!$isValidItem) {
                return false;
            }

            $context->leave();
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
    public function cast(mixed $value, LocalContext $context): array
    {
        if (!\is_iterable($value)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter(new ArrayIndexEntry($index));

            $result[$this->key->cast($index, $context)]
                = $this->value->cast($item, $context);

            $context->leave();
        }

        return $result;
    }
}
