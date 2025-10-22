<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidIterableKeyException;
use TypeLang\Mapper\Exception\Mapping\InvalidIterableValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;

/**
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 * @template-implements TypeInterface<array<TKey, TValue>>
 */
class ArrayType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TKey>
         */
        protected readonly TypeInterface $key = new ArrayKeyType(),
        /**
         * @var TypeInterface<TValue>
         */
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    /**
     * @phpstan-assert-if-true iterable<mixed, mixed>|array<array-key, mixed> $value
     */
    public function match(mixed $value, Context $context): bool
    {
        if ($context->isDenormalization()) {
            return \is_array($value);
        }

        return \is_iterable($value);
    }

    public function cast(mixed $value, Context $context): array
    {
        if (!$this->match($value, $context)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $this->process($value, $context);
    }

    /**
     * @param iterable<mixed, mixed> $value
     * @return array<TKey, TValue>
     * @throws \Throwable
     */
    protected function process(iterable $value, Context $context): array
    {
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
