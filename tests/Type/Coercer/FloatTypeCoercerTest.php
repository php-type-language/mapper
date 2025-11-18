<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Coercer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Coercer\FloatTypeCoercer;
use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;

#[Group('coercer')]
#[CoversClass(FloatTypeCoercer::class)]
final class FloatTypeCoercerTest extends TypeCoercerTestCase
{
    protected static function createCoercer(): TypeCoercerInterface
    {
        return new FloatTypeCoercer();
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCoercionSamples() as $value => $default) {
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
                $value === \PHP_INT_MAX => (float) \PHP_INT_MAX,
                $value === 42 => 42.0,
                $value === 1 => 1.0,
                $value === 0 => 0.0,
                $value === -1 => -1.0,
                $value === -42 => -42.0,
                $value === \PHP_INT_MIN => (float) \PHP_INT_MIN,
                $value === '9223372036854775808' => 9223372036854775808.0,
                $value === '9223372036854775807' => 9223372036854775807.0,
                $value === '42' => 42.0,
                $value === '1' => 1.0,
                $value === '0' => 0.0,
                $value === '-1' => -1.0,
                $value === '-42' => -42.0,
                $value === '-9223372036854775808' => -9223372036854775808.0,
                $value === '-9223372036854775809' => -9223372036854775809.0,
                $value === '9223372036854775808.0' => 9223372036854775808.0,
                $value === '9223372036854775807.0' => 9223372036854775807.0,
                $value === '42.5' => 42.5,
                $value === '42.0' => 42.0,
                $value === '1.0' => 1.0,
                $value === '0.0' => 0.0,
                $value === '-1.0' => -1.0,
                $value === '-42.0' => -42.0,
                $value === '-42.5' => -42.5,
                $value === '-9223372036854775808.0' => -9223372036854775808.0,
                $value === '-9223372036854775809.0' => -9223372036854775809.0,
                $value === null => 0.0,
                $value === true => 1.0,
                $value === false => 0.0,
                $value === IntBackedEnumStub::ExampleCase => (float) IntBackedEnumStub::ExampleCase->value,
                default => $default,
            };
        }
    }
}
