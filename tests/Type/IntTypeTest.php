<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Type\Coercer\IntTypeCoercer;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('types')]
#[CoversClass(IntType::class)]
#[CoversClass(IntTypeCoercer::class)]
final class IntTypeTest extends SymmetricTypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new IntType();
    }

    protected static function matchValues(bool $strict): iterable
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

    protected static function castValues(bool $strict): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42 => 42,
                $value === 1 => 1,
                $value === 0 => 0,
                $value === -1 => -1,
                $value === -42 => -42,
                $value === \PHP_INT_MAX => \PHP_INT_MAX,
                $value === \PHP_INT_MIN => \PHP_INT_MIN,
                // Type casts
                $strict === false => match (true) {
                    // Numeric integer-like string values
                    $value === "42" => 42,
                    $value === "1" => 1,
                    $value === "0" => 0,
                    $value === "-1" => -1,
                    $value === "-42" => -42,
                    // Float values
                    $value === 42.0 => 42,
                    $value === 1.0 => 1,
                    $value === 0.0 => 0,
                    $value === -1.0 => -1,
                    $value === -42.0 => -42,
                    // Numeric float-like string values
                    $value === "42.0" => 42,
                    $value === "1.0" => 1,
                    $value === "0.0" => 0,
                    $value === "-1.0" => -1,
                    $value === "-42.0" => -42,
                    // Null
                    $value === null => 0,
                    // Boolean
                    $value === true => 1,
                    $value === false => 0,
                    // Enum
                    $value === IntBackedEnumStub::ExampleCase => IntBackedEnumStub::ExampleCase->value,
                    default => $default,
                },
                default => $default,
            };
        }
    }
}
