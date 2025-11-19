<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template TResult of mixed = mixed
 */
abstract class LiteralType implements TypeInterface
{
    public function __construct(
        /**
         * @var TResult
         */
        protected readonly mixed $value,
        /**
         * @var TypeInterface<TResult>
         */
        protected readonly TypeInterface $type,
    ) {}

    /**
     * @phpstan-assert-if-true TResult $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        if ($value === $this->value) {
            return true;
        }

        try {
            return $this->type->cast($value, $context) === $this->value;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $coerced = $this->type->cast($value, $context);

        if ($coerced === $this->value) {
            return $coerced;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
