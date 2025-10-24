<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\BoolTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<bool>
 */
class BoolLiteralType implements TypeInterface
{
    public function __construct(
        protected readonly bool $value,
        /**
         * @var TypeCoercerInterface<bool>
         */
        protected readonly TypeCoercerInterface $coercer = new BoolTypeCoercer(),
    ) {}

    /**
     * @phpstan-assert-if-true bool $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): bool
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
