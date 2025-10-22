<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;


class IntLiteralType extends IntType
{
    public function __construct(
        private readonly int $value,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): int
    {
        // Fast return in case of value if not castable
        if ($value === $this->value) {
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            $coerced = $this->coerce($value, $context);

            if ($coerced === $this->value) {
                return $coerced;
            }
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
