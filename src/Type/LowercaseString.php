<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\StringTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<non-empty-string>
 */
class LowercaseString implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<string>
         */
        protected readonly TypeCoercerInterface $coercer = new StringTypeCoercer(),
    ) {}

    /**
     * @phpstan-assert-if-true non-empty-string $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value) && $this->isLowerString($value);
    }

    private function isLowerString(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        if (\function_exists('\\ctype_upper')) {
            return !\ctype_upper($value);
        }

        return \preg_match('/[A-Z]/', $value) <= 0;
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
