<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\NonZeroIntType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(NonZeroIntType::class)]
final class NonZeroIntTypeTest extends CoercibleTypeTestCase
{
    protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface
    {
        if ($coercer !== null) {
            return new NonZeroIntType(coercer: $coercer);
        }

        return new NonZeroIntType();
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === \PHP_INT_MAX,
                $value === \PHP_INT_MIN,
                $value === 42,
                $value === -42,
                $value === 1,
                $value === -1 => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === \PHP_INT_MAX => \PHP_INT_MAX,
                $value === \PHP_INT_MIN => \PHP_INT_MIN,
                $value === 42 => 42,
                $value === -42 => -42,
                $value === 1 => 1,
                $value === -1 => -1,
                default => $default,
            };
        }
    }
}
