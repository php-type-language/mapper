<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\BoolType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(BoolType::class)]
final class BoolTypeTest extends TypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new BoolType();
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === true,
                $value === false => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === true => true,
                $value === false => false,
                default => $default,
            };
        }
    }
}
