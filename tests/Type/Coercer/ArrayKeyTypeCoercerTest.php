<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Coercer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnumStub;
use TypeLang\Mapper\Type\Coercer\ArrayKeyTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

#[Group('coercer')]
#[CoversClass(ArrayKeyTypeCoercer::class)]
final class ArrayKeyTypeCoercerTest extends TypeCoercerTestCase
{
    protected static function createCoercer(): TypeCoercerInterface
    {
        return new ArrayKeyTypeCoercer();
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCoercionSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42 => 42,
                $value === 1 => 1,
                $value === 0 => 0,
                $value === -1 => -1,
                $value === -42 => -42,
                $value === \PHP_INT_MAX => \PHP_INT_MAX,
                $value === \PHP_INT_MIN => \PHP_INT_MIN,
                $value === '9223372036854775808' => '9223372036854775808',
                $value === '9223372036854775807' => '9223372036854775807',
                $value === '42' => '42',
                $value === '1' => '1',
                $value === '0' => '0',
                $value === '-1' => '-1',
                $value === '-42' => '-42',
                $value === '-9223372036854775808' => '-9223372036854775808',
                $value === '-9223372036854775809' => '-9223372036854775809',
                $value === '9223372036854775808.0' => '9223372036854775808.0',
                $value === '9223372036854775807.0' => '9223372036854775807.0',
                $value === '42.5' => '42.5',
                $value === '42.0' => '42.0',
                $value === '1.0' => '1.0',
                $value === '0.0' => '0.0',
                $value === '-1.0' => '-1.0',
                $value === '-42.0' => '-42.0',
                $value === '-42.5' => '-42.5',
                $value === '-9223372036854775808.0' => '-9223372036854775808.0',
                $value === '-9223372036854775809.0' => '-9223372036854775809.0',
                $value === 'true' => 'true',
                $value === 'false' => 'false',
                $value === 'non empty' => 'non empty',
                $value === '' => '',
                $value === "42" => 42,
                $value === "1" => 1,
                $value === "0" => 0,
                $value === "-1" => -1,
                $value === "-42" => -42,
                $value === 42.0 => 42,
                $value === 1.0 => 1,
                $value === 0.0 => 0,
                $value === -1.0 => -1,
                $value === -42.0 => -42,
                $value === "42.0" => 42,
                $value === "1.0" => 1,
                $value === "0.0" => 0,
                $value === "-1.0" => -1,
                $value === "-42.0" => -42,
                $value === null => 0,
                $value === true => 1,
                $value === false => 0,
                $value === IntBackedEnumStub::ExampleCase => IntBackedEnumStub::ExampleCase->value,
                $value === StringBackedEnumStub::ExampleCase => StringBackedEnumStub::ExampleCase->value,
                $value === UnitEnumStub::ExampleCase => UnitEnumStub::ExampleCase->name,
                default => $default,
            };
        }
    }
}
