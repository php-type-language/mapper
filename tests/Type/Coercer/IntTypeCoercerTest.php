<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Coercer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Coercer\IntTypeCoercer;
use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;

#[Group('coercer')]
#[CoversClass(IntTypeCoercer::class)]
final class IntTypeCoercerTest extends TypeCoercerTestCase
{
    protected static function createCoercer(): TypeCoercerInterface
    {
        return new IntTypeCoercer();
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
                $value === '42' => 42,
                $value === '1' => 1,
                $value === '0' => 0,
                $value === '-1' => -1,
                $value === '-42' => -42,
                $value === 42.0 => 42,
                $value === 1.0 => 1,
                $value === 0.0 => 0,
                $value === -1.0 => -1,
                $value === -42.0 => -42,
                $value === '42.0' => 42,
                $value === '1.0' => 1,
                $value === '0.0' => 0,
                $value === '-1.0' => -1,
                $value === '-42.0' => -42,
                $value === null => 0,
                $value === true => 1,
                $value === false => 0,
                // Resource to int type coercion is not obvious:
                // \is_resource($value) => \get_resource_id($value),
                $value === IntBackedEnumStub::ExampleCase => IntBackedEnumStub::ExampleCase->value,
                default => $default,
            };
        }
    }
}
