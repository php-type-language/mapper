<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\FloatTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<float>
 */
class FloatLiteralType implements TypeInterface
{
    private readonly float $value;

    public function __construct(
        int|float $value,
        /**
         * @var TypeCoercerInterface<float>
         */
        protected readonly TypeCoercerInterface $coercer = new FloatTypeCoercer(),
    ) {
        $this->value = (float) $value;
    }

    /**
     * @phpstan-assert-if-true int|float $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): float
    {
        if ($value === $this->value) {
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
