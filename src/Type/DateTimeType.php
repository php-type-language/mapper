<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * Date of {@see \DateTimeInterface} by its string. A string is parsed by
 * the format in case of it is given: the fields absent from the format are
 * zero (`!` is added unless the format has `!` or `|`), an impossible date
 * such as `2021-02-30` is rejected. Without the format a string is parsed
 * by the constructor of the class. An object of the type is denormalized as
 * is. A date is normalized by the format or by RFC 3339.
 *
 * @template T of \DateTimeInterface
 *
 * @template-implements TypeInterface<T>
 */
final class DateTimeType implements TypeInterface
{
    public const DEFAULT_FORMAT = \DateTimeInterface::RFC3339;

    public function __construct(
        /**
         * Class or interface the date is checked by
         *
         * @var class-string<T>
         */
        public readonly string $class,
        /**
         * Class a date is created by, {@see $class} or its implementation
         *
         * @var class-string<\DateTime|\DateTimeImmutable>
         */
        public readonly string $implementation,
        /**
         * Type of the string of a date
         *
         * @var TypeInterface<string>
         */
        public readonly TypeInterface $string,
        /**
         * Canonical description of the type, for messages
         *
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * Format of {@see \DateTimeInterface::format()}, {@see null} in case
         * of any string the constructor accepts and RFC 3339 on output
         *
         * @var non-empty-string|null
         */
        public readonly ?string $format = null,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        if ($value instanceof $this->class) {
            return true;
        }

        return $this->string->isDenormalizable($value, $context)
            && $this->parse($this->string->denormalize($value, $context)) !== null;
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value is not a date
     * @throws \Throwable in case of the value is not a string
     */
    public function denormalize(mixed $value, Context $context): \DateTimeInterface
    {
        if ($value instanceof $this->class) {
            return $value;
        }

        /** @var T */
        return $this->parse($this->string->denormalize($value, $context))
            ?? throw InvalidValueException::becauseInvalidType($this->name, $value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * @throws InvalidValueException in case of the value is not a date of
     *         the type
     */
    public function normalize(mixed $value, Context $context): string
    {
        if (!$value instanceof $this->class) {
            throw InvalidValueException::becauseInvalidType($this->name, $value);
        }

        return $value->format($this->format ?? self::DEFAULT_FORMAT);
    }

    /**
     * Returns {@see null} in case of the string is not a date. An empty
     * string and a string with a NUL byte are not dates.
     */
    private function parse(string $value): ?\DateTimeInterface
    {
        if ($value === '' || \str_contains($value, "\0")) {
            return null;
        }

        if ($this->format === null) {
            try {
                return new ($this->implementation)($value);
            } catch (\Exception) {
                return null;
            }
        }

        $format = $this->format;

        if (!\str_contains($format, '!') && !\str_contains($format, '|')) {
            $format = '!' . $format;
        }

        $result = ($this->implementation)::createFromFormat($format, $value);
        $errors = ($this->implementation)::getLastErrors();

        if ($result === false || ($errors !== false && $errors['warning_count'] + $errors['error_count'] > 0)) {
            return null;
        }

        return $result;
    }
}
