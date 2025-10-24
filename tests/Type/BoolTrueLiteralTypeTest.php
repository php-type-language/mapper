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
final class BoolTrueLiteralTypeTest extends CoercibleTypeTestCase
{
    protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface
    {
        if ($coercer !== null) {
            return new BoolLiteralType(true, coercer: $coercer);
        }

        return new BoolLiteralType(true);
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                // Only true is matches
                $value === true => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                // Only true may casts
                $value === true => true,
                default => $default,
            };
        }
    }
}
