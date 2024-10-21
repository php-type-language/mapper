<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class DateTimeType extends AsymmetricType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_DATETIME_FORMAT = \DateTimeInterface::RFC3339;

    /**
     * @param class-string<\DateTime|\DateTimeImmutable> $class
     */
    public function __construct(
        protected readonly string $class,
        protected readonly ?string $format = null,
    ) {}

    protected function isNormalizable(mixed $value, Context $context): bool
    {
        return $value instanceof \DateTimeInterface;
    }

    /**
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, Context $context): string
    {
        if (!$value instanceof \DateTimeInterface) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $value->format($this->format ?? self::DEFAULT_DATETIME_FORMAT);
    }

    protected function isDenormalizable(mixed $value, Context $context): bool
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

    /**
     * @throws InvalidValueException
     */
    public function denormalize(mixed $value, Context $context): \DateTimeInterface
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
