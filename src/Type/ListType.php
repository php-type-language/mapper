<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Runtime\Context;

class ListType implements TypeInterface
{
    public function __construct(
        private readonly TypeInterface $type = new MixedType(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \is_array($value) && \array_is_list($value);
    }

    /**
     * @return list<mixed>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
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
            $entrance = $context->enter(new ArrayIndexEntry($index));

            $result[] = $this->type->cast($item, $entrance);
        }

        return $result;
    }
}
