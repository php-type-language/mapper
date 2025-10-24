<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\NullType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(NullType::class)]
final class NullTypeTest extends TypeTestCase
{
    protected static function createType(): TypeInterface
    {
        return new NullType();
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === null => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === null => null,
                default => $default,
            };
        }
    }
}
