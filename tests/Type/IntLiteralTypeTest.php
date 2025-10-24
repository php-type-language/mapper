<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\IntLiteralType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(IntLiteralType::class)]
final class IntLiteralTypeTest extends CoercibleTypeTestCase
{
    protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface
    {
        if ($coercer !== null) {
            return new IntLiteralType(42, coercer: $coercer);
        }

        return new IntLiteralType(42);
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42 => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42 => 42,
                default => $default,
            };
        }
    }
}
