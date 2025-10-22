<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\DateTimeType\DateTimeTypeNormalizer;
use TypeLang\Mapper\Type\TypeInterface;

class DateTimeToStringTypeBuilder extends DateTimeTypeBuilder
{
    protected function create(string $class, ?string $format = null): TypeInterface
    {
        return new DateTimeTypeNormalizer($format);
    }
}
