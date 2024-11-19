<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidIterableKeyException;
use TypeLang\Mapper\Exception\Mapping\InvalidIterableValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;

class ArrayType implements TypeInterface
{
    public function __construct(
        protected readonly TypeInterface $key = new ArrayKeyType(),
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        if ($context->isDenormalization()) {
            return \is_array($value);
        }

        return \is_iterable($value);
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidValueException in case the value is incorrect
     * @throws InvalidIterableKeyException in case the key of a certain element is incorrect
     * @throws InvalidIterableValueException in case the value of a certain element is incorrect
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, Context $context): array
    {
        if (!$this->match($value, $context)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        $result = [];
        $index = 0;

        foreach ($value as $key => $item) {
            try {
                $key = $this->key->cast($key, $context);
            } catch (InvalidValueException $e) {
                throw InvalidIterableKeyException::createFromContext(
                    index: $index,
                    key: $key,
                    value: $value,
                    context: $context,
                    previous: $e,
                );
            }

            // Not supported by PHP
            if (!\is_string($key) && !\is_int($key)) {
                throw InvalidIterableKeyException::createFromContext(
                    index: $index,
                    key: $key,
                    value: $value,
                    context: $context,
                );
            }

            $entrance = $context->enter($item, new ArrayIndexEntry($key));

            try {
                $result[$key] = $this->value->cast($item, $entrance);
            } catch (InvalidValueException $e) {
                throw InvalidIterableValueException::createFromContext(
                    element: $item,
                    index: $index,
                    key: $key,
                    value: $value,
                    context: $entrance,
                    previous: $e,
                );
            }

            ++$index;
        }

        return $result;
    }
}
