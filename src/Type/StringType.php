<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class StringType extends NamedType
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
        return \is_string($value);
    }

    /**
     * Converts incoming value to the string (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): string
    {
        if (\is_string($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
