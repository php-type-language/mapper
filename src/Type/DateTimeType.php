<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\DateTimeType\DateTimeTypeDenormalizer;
use TypeLang\Mapper\Type\DateTimeType\DateTimeTypeNormalizer;

/**
 * @template-extends AsymmetricType<DateTimeTypeNormalizer, DateTimeTypeDenormalizer>
 */
class DateTimeType extends AsymmetricType
{
    /**
     * @param class-string<\DateTime|\DateTimeImmutable> $class
     */
    public function __construct(string $class, ?string $format = null)
    {
        parent::__construct(
            normalizer: new DateTimeTypeNormalizer(
                format: $format ?? DateTimeTypeNormalizer::DEFAULT_DATETIME_FORMAT,
            ),
            denormalizer: new DateTimeTypeDenormalizer(
                class: $class,
                format: $format,
            ),
        );
    }
}
