<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\FloatType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(FloatType::class)]
final class FloatTypeTest extends TypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new FloatType();
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === \INF,
                $value === -\INF,
                \is_float($value) && \is_nan($value),
                $value === \PHP_INT_MAX + 1,
                $value === 42.5,
                $value === 42.0,
                $value === 1.0,
                $value === 0.0,
                $value === -1.0,
                $value === -42.0,
                $value === -42.5,
                $value === \PHP_INT_MIN - 1 => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === \INF => \INF,
                $value === -\INF => -\INF,
                \is_float($value) && \is_nan($value) => \NAN,
                $value === \PHP_INT_MAX + 1 => \PHP_INT_MAX + 1,
                $value === 42.5 => 42.5,
                $value === 42.0 => 42.0,
                $value === 1.0 => 1.0,
                $value === 0.0 => 0.0,
                $value === -1.0 => -1.0,
                $value === -42.0 => -42.0,
                $value === -42.5 => -42.5,
                $value === \PHP_INT_MIN - 1 => \PHP_INT_MIN - 1,
                default => $default,
            };
        }
    }
}
