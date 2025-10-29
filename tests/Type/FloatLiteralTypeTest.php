<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\FloatLiteralType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(FloatLiteralType::class)]
final class FloatLiteralTypeTest extends TypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new FloatLiteralType(42);
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42.0 => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42.0 => 42.0,
                default => $default,
            };
        }
    }
}
