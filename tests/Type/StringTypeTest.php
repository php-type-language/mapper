<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnumStub;
use TypeLang\Mapper\Type\ArrayKeyType;
use TypeLang\Mapper\Type\Coercer\ArrayKeyTypeCoercer;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('types')]
#[CoversClass(ArrayKeyType::class)]
#[CoversClass(ArrayKeyTypeCoercer::class)]
final class StringTypeTest extends SymmetricTypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new StringType();
    }

    protected static function matchValues(bool $strict): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === '9223372036854775808',
                $value === '9223372036854775807',
                $value === '42',
                $value === '1',
                $value === '0',
                $value === '-1',
                $value === '-42',
                $value === '-9223372036854775808',
                $value === '-9223372036854775809',
                $value === '9223372036854775808.0',
                $value === '9223372036854775807.0',
                $value === '42.5',
                $value === '42.0',
                $value === '1.0',
                $value === '0.0',
                $value === '-1.0',
                $value === '-42.0',
                $value === '-42.5',
                $value === '-9223372036854775808.0',
                $value === '-9223372036854775809.0',
                $value === 'true',
                $value === 'false',
                $value === 'non empty',
                $value === '' => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $strict): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
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
                // Type casts
                $strict === false => match (true) {
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
                    $value === IntBackedEnumStub::ExampleCase => (string) IntBackedEnumStub::ExampleCase->value,
                    $value === StringBackedEnumStub::ExampleCase => StringBackedEnumStub::ExampleCase->value,
                    $value === UnitEnumStub::ExampleCase => UnitEnumStub::ExampleCase->name,
                    default => $default,
                },
                default => $default,
            };
        }
    }
}
