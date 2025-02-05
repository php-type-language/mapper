<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type\DateTimeType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

class DateTimeTypeNormalizer implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_DATETIME_FORMAT = \DateTimeInterface::RFC3339;

    public function __construct(
        protected readonly string $format = self::DEFAULT_DATETIME_FORMAT,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value instanceof \DateTimeInterface;
    }

    public function cast(mixed $value, Context $context): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->format);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
