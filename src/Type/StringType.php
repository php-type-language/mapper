<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;

class StringType extends SimpleType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'string';

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = self::DEFAULT_TYPE_NAME)
    {
        parent::__construct($name);
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToString($value);
        }

        return \is_string($value);
    }

    /**
     * Converts incoming value to the string (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): string
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToString($value);
        }

        if (\is_string($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }

    /**
     * A method to convert input data to a string representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function tryCastToString(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            $value instanceof \Stringable => (string) $value,
            \is_array($value),
            \is_object($value) => $value,
            $value === true => '1',
            $value === false => '0',
            // @phpstan-ignore-next-line : Any other type can be converted to string
            default => (string) $value,
        };
    }
}
