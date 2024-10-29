<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class FloatLiteralType implements TypeInterface
{
    public function __construct(
        private readonly float|int $value,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value === (float) $this->value;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): float
    {
        if ($value === (float) $this->value) {
            /** @var float */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
