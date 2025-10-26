<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\FloatTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<float>
 */
class FloatType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<float>
         */
        protected readonly TypeCoercerInterface $coercer = new FloatTypeCoercer(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \is_float($value);
    }

    public function cast(mixed $value, Context $context): float
    {
        return match (true) {
            \is_float($value) => $value,
            !$context->isStrictTypesEnabled() => $this->coercer->coerce($value, $context),
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
