<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\StringTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<numeric-string>
 */
class NumericString implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<string>
         */
        protected readonly TypeCoercerInterface $coercer = new StringTypeCoercer(),
    ) {}

    /**
     * @phpstan-assert-if-true numeric-string $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value) && \is_numeric($value);
    }

    public function cast(mixed $value, Context $context): string
    {
        $coerced = $value;

        if (!\is_string($value) && !$context->isStrictTypesEnabled()) {
            $coerced = $this->coercer->coerce($value, $context);
        }

        if ($this->match($coerced, $context)) {
            return $coerced;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
