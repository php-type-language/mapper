<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\DateTimeType\DateTimeTypeDenormalizer;
use TypeLang\Mapper\Type\TypeInterface;

class DateTimeFromStringTypeBuilder extends DateTimeTypeBuilder
{
    protected function create(string $class, ?string $format = null): TypeInterface
    {
        return new DateTimeTypeDenormalizer($class, $format);
    }
}
