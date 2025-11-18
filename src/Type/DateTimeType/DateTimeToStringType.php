<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\DateTimeType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-implements TypeInterface<string>
 */
class DateTimeToStringType implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_DATETIME_FORMAT = \DateTimeInterface::RFC3339;

    public function __construct(
        /**
         * @var non-empty-string|null
         */
        protected readonly ?string $format = null,
    ) {}

    public function match(mixed $value, RuntimeContext $context): bool
    {
        return $value instanceof \DateTimeInterface;
    }

    public function cast(mixed $value, RuntimeContext $context): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->format ?? self::DEFAULT_DATETIME_FORMAT);
        }

        throw InvalidValueException::createFromContext($context);
    }
}
