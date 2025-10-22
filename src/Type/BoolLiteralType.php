<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class BoolLiteralType extends BoolType
{
    public function __construct(
        private readonly bool $value,
    ) {}

    #[\Override]
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    #[\Override]
    public function cast(mixed $value, Context $context): bool
    {
        // Fast return in case of value if not castable
        if ($value === $this->value) {
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            $coerced = $this->coerce($value);

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
