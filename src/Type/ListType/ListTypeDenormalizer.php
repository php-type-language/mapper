<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ListType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;

class ListTypeDenormalizer implements TypeInterface
{
    public function __construct(
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \is_array($value) && \array_is_list($value);
    }

    /**
     * @return list<mixed>
     * @throws InvalidValueException
     * @throws \Throwable
     * @throws RuntimeExceptionInterface
     */
    public function cast(mixed $value, Context $context): array
    {
        if (!\is_array($value) || !\array_is_list($value)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        $result = [];

        foreach ($value as $index => $item) {
            $entrance = $context->enter($item, new ArrayIndexEntry($index));

            $result[] = $this->value->cast($item, $entrance);
        }

        return $result;
    }
}
