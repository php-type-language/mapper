<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\DateTimeType;

use DateTimeImmutable as TDateTime;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TDateTime of \DateTimeImmutable|\DateTime = \DateTimeImmutable
 * @template-implements TypeInterface<TDateTime>
 */
class DateTimeFromStringType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TDateTime>
         */
        protected readonly string $class,
        /**
         * @var non-empty-string|null
         */
        protected readonly ?string $format = null,
        /**
         * @var TypeInterface<string>
         */
        protected readonly TypeInterface $input,
    ) {
        $this->assertDateTimeClassExists($class);
    }

    /**
     * @param class-string<TDateTime> $class
     */
    private function assertDateTimeClassExists(string $class): void
    {
        if (\class_exists($class)) {
            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Creating a date instance requires a date class, but %s is not one',
            $class,
        ));
    }

    /**
     * @phpstan-assert-if-true string $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        if (!$this->input->match($value, $context)) {
            return false;
        }

        try {
            /** @var string $value */
            return $this->tryParseDateTime($value, $context) !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cast(mixed $value, RuntimeContext $context): \DateTimeInterface
    {
        $result = $this->tryParseDateTime(
            value: $this->input->cast($value, $context),
            context: $context,
        );

        if ($result instanceof \DateTimeInterface) {
            /** @var TDateTime */
            return $result;
        }

        throw InvalidValueException::createFromContext($context);
    }

    /**
     * @return TDateTime|null
     */
    private function tryParseDateTime(string $value, RuntimeContext $context): ?\DateTimeInterface
    {
        // In case of format and strict config types are enabled
        if ($this->format !== null && $context->isStrictTypesEnabled()) {
            /** @var class-string<TDateTime> $class */
            $class = $this->class;

            try {
                $result = $class::createFromFormat($this->format, $value);
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
