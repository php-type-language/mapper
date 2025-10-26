<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\DateTimeToStringType;

/**
 * @template TDateTime of \DateTime|\DateTimeImmutable = \DateTimeImmutable
 * @template-extends DateTimeTypeBuilder<TDateTime, string>
 */
class DateTimeToStringTypeBuilder extends DateTimeTypeBuilder
{
    protected function create(string $class, ?string $format = null): DateTimeToStringType
    {
        $format ??= DateTimeToStringType::DEFAULT_DATETIME_FORMAT;

        return new DateTimeToStringType($format);
    }
}
