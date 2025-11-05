<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\DateTimeToStringType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template-extends DateTimeTypeBuilder<string>
 */
class DateTimeToStringTypeBuilder extends DateTimeTypeBuilder
{
    protected function create(NamedTypeNode $stmt, string $class, ?string $format = null): DateTimeToStringType
    {
        $format ??= DateTimeToStringType::DEFAULT_DATETIME_FORMAT;

        return new DateTimeToStringType($format);
    }
}
