<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type\UnitEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

class UnitEnumTypeDenormalizer implements TypeInterface
{
    /**
     * @param class-string<\UnitEnum> $class
     * @param non-empty-list<non-empty-string> $cases
     */
    public function __construct(
        protected readonly string $class,
        protected readonly array $cases,
        protected readonly TypeInterface $string,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \in_array($value, $this->cases, true);
    }

    public function cast(mixed $value, Context $context): \UnitEnum
    {
        $string = $this->string->cast($value, $context);

        if (!$this->match($string, $context)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        try {
            // @phpstan-ignore-next-line : Handle Error manually
            return \constant($this->class . '::' . $string);
        } catch (\Error $e) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
                previous: $e,
            );
        }
    }
}
