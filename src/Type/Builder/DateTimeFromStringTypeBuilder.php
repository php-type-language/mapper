<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\DateTimeFromStringType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template TDateTime of \DateTimeInterface = \DateTimeInterface
 * @template-extends DateTimeTypeBuilder<TDateTime, TDateTime>
 */
class DateTimeFromStringTypeBuilder extends DateTimeTypeBuilder
{
    /**
     * @var array<class-string<\DateTimeInterface>, class-string<\DateTimeInterface>>
     */
    private const DATE_INTERFACE_MAPPING = [
        \DateTimeInterface::class => \DateTimeImmutable::class,
        \Carbon\CarbonInterface::class => \Carbon\CarbonImmutable::class,
    ];

    protected function create(NamedTypeNode $stmt, string $class, ?string $format = null): DateTimeFromStringType
    {
        if (\interface_exists($class)) {
            foreach (self::DATE_INTERFACE_MAPPING as $interface => $impl) {
                if (\is_a($interface, $class, true)) {
                    $class = $impl;
                    break;
                }
            }
        }

        if (!\class_exists($class)) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'To create a date object from a string, a class must be specified, but {{type}} is not one'
            );
        }

        return new DateTimeFromStringType($class, $format);
    }
}
