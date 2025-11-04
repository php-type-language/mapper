<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use DateTime as TDateTime;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

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
        /**
         * @var TypeInterface<string>
         */
        protected readonly TypeInterface $input = new StringType(),
    ) {}

    /**
     * @phpstan-assert-if-true string $value
     */
    public function match(mixed $value, Context $context): bool
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

    public function cast(mixed $value, Context $context): \DateTimeInterface
    {
        $result = $this->tryParseDateTime(
            value: $this->input->cast($value, $context),
        );

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
