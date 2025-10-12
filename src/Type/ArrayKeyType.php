<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeInterface<array-key>
 */
class ArrayKeyType implements TypeInterface
{
    public function __construct(
        protected readonly TypeInterface $string = new StringType(),
        protected readonly TypeInterface $int = new IntType(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        // TBD (?)
        // It is not entirely clear whether a zero ("0") string
        // key should be allowed, since it is technically
        // impossible to put it in associative array.
        //
        // if ($value === '0') {
        //     return false;
        // }

        return $this->int->match($value, $context)
            || $this->string->match($value, $context);
    }

    public function cast(mixed $value, Context $context): string|int
    {
        // PHP does not support numeric string array keys,
        // so we need to force-cast it to the integer value.
        $isIntNumeric = \is_string($value)
            && \is_numeric($value)
            && (float) $value === (float) (int) $value;

        if ($isIntNumeric) {
            return (int) $value;
        }

        if (\is_string($value) || \is_int($value)) {
            /** @var string|int */
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            return $this->coerce($value, $context);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }

    /**
     * @throws \Throwable
     */
    protected function coerce(mixed $value, Context $context): string|int
    {
        try {
            /** @var int */
            return $this->int->cast($value, $context);
        } catch (InvalidValueException) {
            // NaN, -INF and INF cannot be converted to
            // array-key implicitly without losses.
            if (\is_float($value) && !\is_finite($value)) {
                throw InvalidValueException::createFromContext(
                    value: $value,
                    context: $context,
                );
            }

            /** @var string */
            return $this->string->cast($value, $context);
        }
    }
}
