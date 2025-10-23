<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\Coercer\FloatTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

class FloatLiteralType extends FloatType
{
    private readonly float $value;

    /**
     * @param TypeCoercerInterface<float> $coercer
     */
    public function __construct(
        int|float $value,
        TypeCoercerInterface $coercer = new FloatTypeCoercer(),
    ) {
        $this->value = (float) $value;

        parent::__construct($coercer);
    }

    /**
     * @phpstan-assert-if-true int|float $value
     */
    public function match(mixed $value, Context $context): bool
    {
        if (\is_int($value)) {
            return (float) $value === $this->value;
        }

        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): float
    {
        if ($this->match($value, $context)) {
            return (float) $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            $coerced = $this->coercer->coerce($value, $context);

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
