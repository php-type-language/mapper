<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type;

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
        if ($value === $this->value) {
            return $value;
        }

        if (!$context->isStrictTypesEnabled()
            && $this->convertToBool($value) === $this->value) {
            return $this->value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
