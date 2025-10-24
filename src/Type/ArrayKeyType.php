<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\ArrayKeyTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<array-key>
 */
class ArrayKeyType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<string>
         */
        protected readonly TypeInterface $string = new StringType(),
        /**
         * @var TypeInterface<int>
         */
        protected readonly TypeInterface $int = new IntType(),
        /**
         * @var TypeCoercerInterface<array-key>
         */
        protected readonly TypeCoercerInterface $coercer = new ArrayKeyTypeCoercer(),
    ) {}

    /**
     * @phpstan-assert-if-true array-key $value
     */
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
        return match (true) {
            \is_string($value),
            \is_int($value) => $value,
            !$context->isStrictTypesEnabled() => $this->coercer->coerce($value, $context),
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
