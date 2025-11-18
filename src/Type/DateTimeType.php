<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\DateTimeType\DateTimeFromStringType;
use TypeLang\Mapper\Type\DateTimeType\DateTimeToStringType;

/**
 * @template TDateTime of \DateTimeImmutable|\DateTime = \DateTimeImmutable
 *
 * @template-extends AsymmetricType<string, TDateTime|string>
 */
class DateTimeType extends AsymmetricType
{
    /**
     * @param class-string<TDateTime> $class
     * @param non-empty-string|null $format
     * @param TypeInterface<string> $input
     */
    public function __construct(
        string $class,
        ?string $format = null,
        TypeInterface $input = new StringType(),
    ) {
        parent::__construct(
            normalize: new DateTimeToStringType(
                format: $format,
            ),
            denormalize: new DateTimeFromStringType(
                class: $class,
                format: $format,
                input: $input,
            ),
        );
    }
}
