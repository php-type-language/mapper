<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\IntTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<int>
 */
class IntType implements TypeInterface
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
        return \is_int($value);
    }

    public function cast(mixed $value, Context $context): int
    {
        return match (true) {
            \is_int($value) => $value,
            !$context->isStrictTypesEnabled() => $this->coercer->coerce($value, $context),
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
