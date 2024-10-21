<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;

class NonEmpty implements TypeInterface
{
    public function __construct(
        protected readonly TypeInterface $type,
    ) {}

    protected function isEmpty(mixed $value): bool
    {
        return $value === '' || $value === [] || $value === null;
    }

    public function match(mixed $value, Context $context): bool
    {
        return !$this->isEmpty($value)
            && $this->type->match($value, $context);
    }

    /**
     * @throws InvalidValueException
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
     */
    public function cast(mixed $value, Context $context): mixed
    {
        if (!$this->isEmpty($value)) {
            return $this->type->cast($value, $context);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
