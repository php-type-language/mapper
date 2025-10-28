<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template T of mixed = mixed
 *
 * @template-implements TypeInterface<T>
 */
class LiteralType implements TypeInterface
{
    public function __construct(
        /**
         * @var T
         */
        protected readonly mixed $value,
        /**
         * @var TypeCoercerInterface<T>
         */
        protected readonly TypeCoercerInterface $coercer,
    ) {}

    /**
     * @phpstan-assert-if-true T $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): mixed
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
