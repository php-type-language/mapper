<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ArrayType;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Type\ArrayKeyType;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;

class ArrayTypeNormalizer implements TypeInterface
{
    public function __construct(
        protected readonly TypeInterface $key = new ArrayKeyType(),
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \is_iterable($value);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>
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

        $index = 0;
        foreach ($value as $key => $item) {
            $keyEntrance = $context->enter($item, new ArrayIndexEntry($index));

            $key = $this->key->cast($key, $keyEntrance);

            // An output PHP array keys cannot physically contain
            // anything other than int or float
            if (!\is_string($key) && !\is_int($key)) {
                throw InvalidValueException::createFromContext(
                    value: $value,
                    context: $keyEntrance,
                );
            }

            $valueEntrance = $context->enter($item, new ArrayIndexEntry($key));

            $result[$key] = $this->value->cast($item, $valueEntrance);

            ++$index;
        }

        return $result;
    }
}
