<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\Specifier\IntRangeSpecifier;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(IntType::class)]
final class IntRangeTypeTest extends CoercibleTypeTestCase
{
    protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface
    {
        $specifier = new IntRangeSpecifier(-1, 1);

        if ($coercer !== null) {
            return new IntType(coercer: $coercer, specifier: $specifier);
        }

        return new IntType(specifier: $specifier);
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42,
                $value === 1,
                $value === 0,
                $value === -1,
                $value === -42,
                $value === \PHP_INT_MAX,
                $value === \PHP_INT_MIN => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => $normalize
                ? match (true) {
                    $value === 42 => 42,
                    $value === 1 => 1,
                    $value === 0 => 0,
                    $value === -1 => -1,
                    $value === -42 => -42,
                    $value === \PHP_INT_MAX => \PHP_INT_MAX,
                    $value === \PHP_INT_MIN => \PHP_INT_MIN,
                    default => $default,
                }
                : match (true) {
                    $value === 1 => 1,
                    $value === 0 => 0,
                    $value === -1 => -1,
                    default => $default,
                };
        }
    }
}
