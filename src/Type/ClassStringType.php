<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class ClassStringType implements TypeInterface
{
    /**
     * @param non-empty-string|null $class
     */
    public function __construct(
        private readonly ?string $class = null,
    ) {}

    public function match(mixed $value, LocalContext $context): bool
    {
        $isValidString = $value !== '' && \is_string($value);

        if (!$isValidString) {
            return false;
        }

        if ($this->class === null) {
            return \class_exists($value);
        }

        return \is_a($value, $this->class, true);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): string
    {
        if ($this->match($value, $context)) {
            /** @var class-string */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
