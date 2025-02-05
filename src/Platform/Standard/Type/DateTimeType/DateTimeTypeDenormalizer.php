<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type\DateTimeType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

class DateTimeTypeDenormalizer implements TypeInterface
{
    /**
     * @param class-string<\DateTime|\DateTimeImmutable> $class
     */
    public function __construct(
        protected readonly string $class,
        protected readonly ?string $format = null,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        if (!\is_string($value)) {
            return false;
        }

        try {
            return $this->tryParseDateTime($value) !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cast(mixed $value, Context $context): \DateTimeInterface
    {
        if (!\is_string($value)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        $result = $this->tryParseDateTime($value);

        if ($result instanceof \DateTimeInterface) {
            return $result;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }

    private function tryParseDateTime(string $value): ?\DateTimeInterface
    {
        if ($this->format !== null) {
            try {
                $result = ($this->class)::createFromFormat($this->format, $value);
            } catch (\Throwable) {
                return null;
            }

            return \is_bool($result) ? null : $result;
        }

        try {
            return new $this->class($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
