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
class IntLiteralType implements TypeInterface
{
    public function __construct(
        protected readonly int $value,
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
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): int
    {
        // Fast return in case of value if not castable
        if ($value === $this->value) {
            return $value;
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
