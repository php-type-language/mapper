<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\DateTimeFromStringType;

/**
 * @template TDateTime of \DateTime|\DateTimeImmutable = \DateTimeImmutable
 * @template-extends DateTimeTypeBuilder<TDateTime, TDateTime>
 */
class DateTimeFromStringTypeBuilder extends DateTimeTypeBuilder
{
    protected function create(string $class, ?string $format = null): DateTimeFromStringType
    {
        return new DateTimeFromStringType($class, $format);
    }
}
