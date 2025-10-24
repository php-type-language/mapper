<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(IntType::class)]
final class IntTypeTest extends CoercibleTypeTestCase
{
    protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface
    {
        if ($coercer !== null) {
            return new IntType(coercer: $coercer);
        }

        return new IntType();
    }

    public function testCallsCoercion(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('on-coerce');

        $coercer = $this->createMock(TypeCoercerInterface::class);
        $coercer->method('coerce')
            ->willThrowException(new \BadMethodCallException('on-coerce'));

        $type = new IntType($coercer);
        $type->cast(
            value: \fopen('php://memory', 'rb'),
            context: $this->createNormalizationContext('42', false),
        );
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42,
                $value === 1,
                $value === 0,
                $value === -1,
                $value === -42,
                $value === \PHP_INT_MAX,
                $value === \PHP_INT_MIN => true,
                default => $default,
            };
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $default) {
            yield $value => match (true) {
                $value === 42 => 42,
                $value === 1 => 1,
                $value === 0 => 0,
                $value === -1 => -1,
                $value === -42 => -42,
                $value === \PHP_INT_MAX => \PHP_INT_MAX,
                $value === \PHP_INT_MIN => \PHP_INT_MIN,
                default => $default,
            };
        }
    }
}
