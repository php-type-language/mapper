<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Context\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Exception\Runtime\InvalidIterableKeyException;
use TypeLang\Mapper\Exception\Runtime\InvalidIterableValueException;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 * @template-implements TypeInterface<array<TKey, TValue>>
 */
class IterableToArrayType implements TypeInterface
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
     * @phpstan-assert-if-true iterable<mixed, mixed> $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return \is_iterable($value);
    }

    public function cast(mixed $value, RuntimeContext $context): array
    {
        if (!\is_iterable($value)) {
            throw InvalidValueException::createFromContext($context);
        }

        return $this->process($value, $context);
    }

    /**
     * @param iterable<mixed, mixed> $value
     *
     * @return array<TKey, TValue>
     * @throws \Throwable
     */
    protected function process(iterable $value, RuntimeContext $context): array
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
                    context: $entrance,
                    previous: $e,
                );
            }

            ++$index;
        }

        return $result;
    }
}
