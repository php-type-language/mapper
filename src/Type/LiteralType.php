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
    ) {}

    /**
     * @phpstan-assert-if-true TResult $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        if ($value === $this->value) {
            return $value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
