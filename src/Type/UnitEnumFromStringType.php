<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 * @template-implements TypeInterface<TEnum>
 */
class UnitEnumFromStringType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TEnum>
         */
        protected readonly string $class,
        /**
         * @var non-empty-list<non-empty-string>
         */
        protected readonly array $cases,
        /**
         * @var TypeInterface<string>
         */
        protected readonly TypeInterface $string = new StringType(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \in_array($value, $this->cases, true);
    }

    public function cast(mixed $value, Context $context): \UnitEnum
    {
        $string = $this->string->cast($value, $context);

        if (!$this->match($string, $context)) {
            throw InvalidValueException::createFromContext($context);
        }

        try {
            // @phpstan-ignore-next-line : Handle Error manually
            return \constant($this->class . '::' . $string);
        } catch (\Error $e) {
            throw InvalidValueException::createFromContext(
                context: $context,
                previous: $e,
            );
        }
    }
}
