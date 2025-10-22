<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\DateTimeType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TDateTime of \DateTime|\DateTimeImmutable = \DateTimeImmutable
 * @template-implements TypeInterface<TDateTime>
 */
class DateTimeFromStringType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TDateTime>
         */
        protected readonly string $class,
        protected readonly ?string $format = null,
    ) {}

    /**
     * @phpstan-assert-if-true string $value
     */
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
            /** @var TDateTime */
            return $result;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }

    /**
     * @return TDateTime|null
     */
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
