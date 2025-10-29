<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\IntRangeType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(IntRangeType::class)]
final class IntRangeTypeTest extends TypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new IntRangeType(-1, 1);
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 1,
                $value === 0,
                $value === -1 => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 1 => 1,
                $value === 0 => 0,
                $value === -1 => -1,
                default => $default,
            };
        }
    }
}
