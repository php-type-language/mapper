<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\BoolLiteralType;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(BoolLiteralType::class)]
final class BoolFalseLiteralTypeTest extends CoercibleTypeTestCase
{
    protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface
    {
        if ($coercer !== null) {
            return new BoolLiteralType(false, coercer: $coercer);
        }

        return new BoolLiteralType(false);
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                // Only false is matching
                $value === false => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                // Only false may cast to false
                $value === false => false,
                default => $default,
            };
        }
    }
}
