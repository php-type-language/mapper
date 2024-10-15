<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class FloatLiteralType extends FloatType
{
    public function __construct(
        private readonly float|int $value,
    ) {}

    #[\Override]
    public function match(mixed $value, LocalContext $context): bool
    {
        return $value === (float) $this->value;
    }

    /**
     * @throws InvalidValueException
     */
    #[\Override]
    public function cast(mixed $value, LocalContext $context): float
    {
        if ($this->match($value, $context)) {
            /** @var float */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
