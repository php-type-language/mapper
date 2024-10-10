<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;

class NonEmpty extends GenericType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'non-empty';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        TypeInterface $type,
        string $name = self::DEFAULT_TYPE_NAME,
    ) {
        parent::__construct($type, $name);
    }

    protected function isEmpty(mixed $value): bool
    {
        return $value === '' || $value === [] || $value === null;
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return !$this->isEmpty($value)
            && $this->type->match($value, $context);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): mixed
    {
        if (!$this->isEmpty($value)) {
            return $this->type->cast($value, $context);
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
