<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\IntTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<int>
 */
class NonZeroIntType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<int>
         */
        protected readonly TypeCoercerInterface $coercer = new IntTypeCoercer(),
    ) {}

    /**
     * @phpstan-assert-if-true int $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value) && $value !== 0;
    }

    public function cast(mixed $value, Context $context): int
    {
        $coerced = $value;

        if (!\is_int($value) && !$context->isStrictTypesEnabled()) {
            $coerced = $this->coercer->coerce($value, $context);
        }

        if (\is_int($coerced) && $coerced !== 0) {
            return $coerced;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
