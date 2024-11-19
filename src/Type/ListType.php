<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidIterableValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;

class ListType implements TypeInterface
{
    public function __construct(
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    /**
     * @return ($value is iterable ? bool : false)
     */
    public function match(mixed $value, Context $context): bool
    {
        if ($context->isDenormalization()) {
            return \is_array($value) && \array_is_list($value);
        }

        return \is_iterable($value);
    }

    /**
     * @return list<mixed>
     * @throws InvalidValueException in case the value is incorrect
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
            $entrance = $context->enter($item, new ArrayIndexEntry($index));

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
