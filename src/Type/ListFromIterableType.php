<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Exception\Mapping\InvalidIterableValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;

/**
 * @template-covariant TItem of mixed = mixed
 * @template-implements TypeInterface<list<TItem>>
 */
class ListFromIterableType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TItem>
         */
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    /**
     * @phpstan-assert-if-true iterable<mixed, mixed> $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_iterable($value);
    }

    public function cast(mixed $value, Context $context): array
    {
        if (!\is_iterable($value)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $this->process($value, $context);
    }

    /**
     * @param iterable<mixed, mixed> $value
     *
     * @return list<TItem>
     * @throws \Throwable
     */
    protected function process(iterable $value, Context $context): array
    {
        $result = [];
        $index = 0;

        /** @var iterable<mixed, mixed> $value */
        foreach ($value as $key => $item) {
            $entrance = $context->enter(
                value: $item,
                entry: new ArrayIndexEntry($index),
            );

            try {
                $result[] = $this->value->cast($item, $entrance);
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
