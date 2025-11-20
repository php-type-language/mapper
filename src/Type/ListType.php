<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidIterableValueException;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-covariant TItem of mixed = mixed
 *
 * @template-implements TypeInterface<list<TItem>>
 */
class ListType implements TypeInterface
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
    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        return MatchedResult::successIf($value, \is_array($value) && \array_is_list($value));
    }

    public function cast(mixed $value, RuntimeContext $context): array
    {
        if (!\is_array($value) || !\array_is_list($value)) {
            throw InvalidValueException::createFromContext($context);
        }

        return $this->process($value, $context);
    }

    /**
     * @param iterable<mixed, mixed> $value
     *
     * @return list<TItem>
     * @throws \Throwable
     */
    protected function process(iterable $value, RuntimeContext $context): array
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
                    context: $entrance,
                    previous: $e,
                );
            }

            ++$index;
        }

        return $result;
    }
}
