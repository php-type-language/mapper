<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

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
        protected readonly ?string $format = null,
        /**
         * @var TypeInterface<string>
         */
        protected readonly TypeInterface $input = new StringType(),
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
            return $this->tryParseDateTime($value) !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cast(mixed $value, RuntimeContext $context): \DateTimeInterface
    {
        $result = $this->tryParseDateTime(
            value: $this->input->cast($value, $context),
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
    private function tryParseDateTime(string $value): ?\DateTimeInterface
    {
        if ($this->format !== null) {
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
