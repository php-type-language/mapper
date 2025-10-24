<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\BoolTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<bool>
 */
class BoolType implements TypeInterface
{
    public function __construct(
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
        return \is_bool($value);
    }

    public function cast(mixed $value, Context $context): bool
    {
        return match (true) {
            \is_bool($value) => $value,
            !$context->isStrictTypesEnabled() => $this->coercer->coerce($value, $context),
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
