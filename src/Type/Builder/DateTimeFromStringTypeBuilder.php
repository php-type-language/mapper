<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\DateTimeFromStringType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template-extends DateTimeTypeBuilder<\DateTimeImmutable|\DateTime>
 */
class DateTimeFromStringTypeBuilder extends DateTimeTypeBuilder
{
    private const DATE_INTERFACE_MAPPING = [
        \DateTimeInterface::class => \DateTimeImmutable::class,
        /** @phpstan-ignore-next-line : Classes and interfaces may be missing */
        \Carbon\CarbonInterface::class => \Carbon\CarbonImmutable::class,
    ];

    /**
     * @param class-string<\DateTimeInterface> $class
     *
     * @return class-string<\DateTimeInterface>
     */
    private function resolveClassName(string $class): string
    {
        if (!\interface_exists($class)) {
            return $class;
        }

        foreach (self::DATE_INTERFACE_MAPPING as $interface => $impl) {
            if (\is_a($interface, $class, true)) {
                $class = $impl;
                break;
            }
        }

        /** @var class-string<\DateTimeInterface> */
        return $class;
    }

    protected function create(NamedTypeNode $stmt, string $class, ?string $format = null): DateTimeFromStringType
    {
        $class = $this->resolveClassName($class);

        if (!\class_exists($class)) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'To create a date object from a string, a class must be specified, but {{type}} is not one',
            );
        }

        return new DateTimeFromStringType($class, $format);
    }
}
