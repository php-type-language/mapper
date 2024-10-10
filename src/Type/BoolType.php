<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;

class BoolType extends SimpleType
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_TYPE_NAME = 'bool';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name = self::DEFAULT_TYPE_NAME,
    ) {
        parent::__construct($name);
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToBool($value);
        }

        return \is_bool($value);
    }

    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToBool($value);
        }

        if (\is_bool($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: 'bool',
            context: $context,
        );
    }

    /**
     * A method to convert input data to a bool representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    protected function tryCastToBool(mixed $value): bool
    {
        return match (true) {
            \is_array($value) => $value !== [],
            \is_object($value) => true,
            \is_string($value) => $value !== '',
            default => (bool) $value,
        };
    }
}
