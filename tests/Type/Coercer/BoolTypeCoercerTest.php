<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Coercer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Coercer\BoolTypeCoercer;
use TypeLang\Mapper\Coercer\TypeCoercerInterface;

#[Group('coercer')]
#[CoversClass(BoolTypeCoercer::class)]
final class BoolTypeCoercerTest extends TypeCoercerTestCase
{
    protected static function createCoercer(): TypeCoercerInterface
    {
        return new BoolTypeCoercer();
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCoercionSamples() as $value => $default) {
            yield $value => match (true) {
                $value === false,
                $value === null,
                $value === 0,
                $value === 0.0,
                $value === '0',
                $value === [],
                $value === '' => false,
                default => true,
            };
        }
    }
}
