<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Coercer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnumStub;
use TypeLang\Mapper\Type\Coercer\StringTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

#[Group('coercer')]
#[CoversClass(StringTypeCoercer::class)]
final class StringTypeCoercerTest extends TypeCoercerTestCase
{
    protected static function createCoercer(): TypeCoercerInterface
    {
        return new StringTypeCoercer();
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCoercionSamples() as $value => $default) {
            yield $value => match (true) {
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
                $value === \PHP_INT_MAX + 1 => '9223372036854775808.0',
                $value === \PHP_INT_MAX => '9223372036854775807',
                $value === 42 => '42',
                $value === 1 => '1',
                $value === 0 => '0',
                $value === -1 => '-1',
                $value === -42 => '-42',
                $value === \PHP_INT_MIN => '-9223372036854775808',
                $value === \PHP_INT_MIN - 1 => '-9223372036854775808.0',
                $value === 42.0 => '42.0',
                $value === 42.5 => '42.5',
                $value === 1.0 => '1.0',
                $value === 0.0 => '0.0',
                $value === -1.0 => '-1.0',
                $value === -42.0 => '-42.0',
                $value === -42.5 => '-42.5',
                $value === null => '',
                $value === true => 'true',
                $value === false => 'false',
                $value === \INF => 'inf',
                $value === -\INF => '-inf',
                \is_float($value) && \is_nan($value) => 'nan',
                \is_resource($value) => match (\get_resource_type($value)) {
                    'stream' => 'stream',
                    default => $default,
                },
                \get_debug_type($value) === 'resource (closed)' => 'resource',
                $value === IntBackedEnumStub::ExampleCase => (string) IntBackedEnumStub::ExampleCase->value,
                $value === StringBackedEnumStub::ExampleCase => StringBackedEnumStub::ExampleCase->value,
                $value === UnitEnumStub::ExampleCase => UnitEnumStub::ExampleCase->name,
                default => $default,
            };
        }
    }
}
