<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnum;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnum;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnum;
use TypeLang\Mapper\Type\IntType;

final class IntTypeTest extends TypeTestCase
{
    private function createContext(bool $strictTypes = false): RootContext
    {
        $config = new Configuration(isStrictTypes: $strictTypes);

        return RootContext::forDenormalization(
            value: null,
            config: $config,
            extractor: $this->createMock(TypeExtractorInterface::class),
            parser: $this->createMock(TypeParserInterface::class),
            types: $this->createMock(TypeRepositoryInterface::class),
        );
    }

    // ========================================
    // Data Providers
    // ========================================

    /**
     * @return array<non-empty-string, array{mixed, bool, bool}>
     */
    public static function matchDataProvider(): array
    {
        return [
            // Positive cases
            'int(42)' => [42, true, false],
            'int(0)' => [0, true, false],
            'int(negative)' => [-42, true, false],
            'int(max)' => [\PHP_INT_MAX, true, false],
            'int(min)' => [\PHP_INT_MIN, true, false],

            // Negative cases - strict mode doesn't affect match()
            'string(42)' => ['42', false, false],
            'string(empty)' => ['', false, false],
            'string(text)' => ['hello', false, false],
            'float(3.14)' => [3.14, false, false],
            'float(5.0)' => [5.0, false, false],
            'float(0.0)' => [0.0, false, false],
            'bool(true)' => [true, false, false],
            'bool(false)' => [false, false, false],
            'null' => [null, false, false],
            'array(empty)' => [[], false, false],
            'array(values)' => [[1, 2, 3], false, false],
            'object(stdClass)' => [new \stdClass(), false, false],
        ];
    }

    /**
     * @return array<non-empty-string, array{mixed, int, bool}>
     */
    public static function castSuccessDataProvider(): array
    {
        return [
            // Int inputs (work in both modes)
            'int(42)' => [42, 42, false],
            'int(42) strict' => [42, 42, true],
            'int(0)' => [0, 0, false],
            'int(0) strict' => [0, 0, true],
            'int(negative)' => [-42, -42, false],
            'int(negative) strict' => [-42, -42, true],
            'int(max)' => [\PHP_INT_MAX, \PHP_INT_MAX, false],
            'int(max) strict' => [\PHP_INT_MAX, \PHP_INT_MAX, true],
            'int(min)' => [\PHP_INT_MIN, \PHP_INT_MIN, false],
            'int(min) strict' => [\PHP_INT_MIN, \PHP_INT_MIN, true],

            // Non-strict coercions - boolean
            'non-strict + true' => [true, 1, false],
            'non-strict + false' => [false, 0, false],

            // Non-strict coercions - null
            'non-strict + null' => [null, 0, false],

            // Non-strict coercions - numeric strings
            'non-strict + string(42)' => ['42', 42, false],
            'non-strict + string(-42)' => ['-42', -42, false],
            'non-strict + string(0)' => ['0', 0, false],
            'non-strict + string(123)' => ['123', 123, false],

            // Non-strict coercions - integer-like floats
            'non-strict + float(5.0)' => [5.0, 5, false],
            'non-strict + float(0.0)' => [0.0, 0, false],
            'non-strict + float(-10.0)' => [-10.0, -10, false],
            'non-strict + float(100.0)' => [100.0, 100, false],
        ];
    }

    /**
     * @return array<non-empty-string, array{mixed, bool}>
     */
    public static function castExceptionDataProvider(): array
    {
        return [
            // Strict mode exceptions
            'strict + string(42)' => ['42', true],
            'strict + string(text)' => ['hello', true],
            'strict + true' => [true, true],
            'strict + false' => [false, true],
            'strict + null' => [null, true],
            'strict + float(3.14)' => [3.14, true],
            'strict + float(5.0)' => [5.0, true],
            'strict + array' => [[1, 2, 3], true],
            'strict + object' => [new \stdClass(), true],

            // Non-strict exceptions (unconvertible types)
            'non-strict + string(non-numeric)' => ['hello', false],
            'non-strict + string(empty)' => ['', false],
            'non-strict + string(float-text)' => ['3.14abc', false],
            'non-strict + float(3.14)' => [3.14, false],
            'non-strict + float(precision-loss)' => [3.5, false],
            'non-strict + array' => [[1, 2, 3], false],
            'non-strict + object(stdClass)' => [new \stdClass(), false],
            'non-strict + nan' => [\NAN, false],
            'non-strict + inf' => [\INF, false],
            'non-strict + -inf' => [-\INF, false],
        ];
    }

    // ========================================
    // Match Tests
    // ========================================

    #[DataProvider('matchDataProvider')]
    public function testMatch(mixed $value, bool $expectedMatch, bool $strictTypes): void
    {
        $type = new IntType();
        $context = $this->createContext($strictTypes);

        self::assertSame($expectedMatch, $type->match($value, $context));
    }

    // ========================================
    // Cast Success Tests
    // ========================================

    #[DataProvider('castSuccessDataProvider')]
    public function testCast(mixed $value, int $expected, bool $strictTypes): void
    {
        $type = new IntType();
        $context = $this->createContext($strictTypes);

        self::assertSame($expected, $type->cast($value, $context));
    }

    // ========================================
    // Cast Exception Tests
    // ========================================

    #[DataProvider('castExceptionDataProvider')]
    public function testCastThrowsException(mixed $value, bool $strictTypes): void
    {
        $type = new IntType();
        $context = $this->createContext($strictTypes);

        $this->expectException(InvalidValueException::class);
        $type->cast($value, $context);
    }

    // ========================================
    // Backed Enum Tests
    // ========================================

    public function testCastCoercesIntBackedEnum(): void
    {
        $enum = IntBackedEnum::Case1;

        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(1, $type->cast($enum, $context));
    }

    public function testCastCoercesIntBackedEnumCase2(): void
    {
        $enum = IntBackedEnum::Case2;

        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(2, $type->cast($enum, $context));
    }

    public function testCastThrowsExceptionForStringBackedEnumInNonStrictMode(): void
    {
        $enum = StringBackedEnum::Case1;

        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast($enum, $context);
    }

    public function testCastThrowsExceptionForIntBackedEnumInStrictMode(): void
    {
        $enum = IntBackedEnum::Case1;

        $type = new IntType();
        $context = $this->createContext(strictTypes: true);

        $this->expectException(InvalidValueException::class);
        $type->cast($enum, $context);
    }

    public function testCastThrowsExceptionForUnitEnumInNonStrictMode(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(UnitEnum::Case1, $context);
    }

    // ========================================
    // Precision Loss Tests
    // ========================================

    public function testCastThrowsExceptionForFloatWithPrecisionLoss(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(3.14, $context);
    }

    public function testCastThrowsExceptionForFloatWithSmallPrecisionLoss(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(42.1, $context);
    }

    public function testCastThrowsExceptionForFloatWithTinyPrecisionLoss(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(1.0000001, $context);
    }

    public function testCastAllowsIntegerLikeFloatWithoutPrecisionLoss(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(42, $type->cast(42.0, $context));
    }

    public function testCastAllowsNegativeIntegerLikeFloat(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(-100, $type->cast(-100.0, $context));
    }

    public function testCastThrowsExceptionForNan(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(\NAN, $context);
    }

    public function testCastThrowsExceptionForInfinity(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(\INF, $context);
    }

    public function testCastThrowsExceptionForNegativeInfinity(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(-\INF, $context);
    }

    // ========================================
    // Numeric String Tests
    // ========================================

    public function testCastCoercesNumericStringWithLeadingZeros(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(42, $type->cast('042', $context));
    }

    public function testCastCoercesNumericStringWithPositiveSign(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(42, $type->cast('+42', $context));
    }

    public function testCastCoercesNumericStringWithSpaces(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(42, $type->cast('  42  ', $context));
    }

    public function testCastThrowsExceptionForPartiallyNumericString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast('42abc', $context);
    }

    public function testCastThrowsExceptionForStringWithInternalSpaces(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast('4 2', $context);
    }

    public function testCastThrowsExceptionForHexString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast('0x2A', $context);
    }

    #[DoesNotPerformAssertions]
    public function testCastThrowsExceptionForOctalString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $type->cast('052', $context);
    }

    #[DoesNotPerformAssertions]
    public function testCastThrowsExceptionForScientificNotationString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $type->cast('1e2', $context);
    }

    // ========================================
    // Resource and Closure Tests
    // ========================================

    public function testCastThrowsExceptionForResourceInNonStrictMode(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $resource = \fopen('php://memory', 'r');

        $result = $type->cast($resource, $context);
        self::assertSame(\get_resource_id($resource), $result);
    }

    public function testCastThrowsExceptionForClosureInNonStrictMode(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(fn() => 42, $context);
    }

    // ========================================
    // Edge Cases
    // ========================================

    public function testCastHandlesZeroString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(0, $type->cast('0', $context));
    }

    public function testCastHandlesNegativeZeroString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(0, $type->cast('-0', $context));
    }

    public function testCastHandlesNegativeZeroFloat(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(0, $type->cast(-0.0, $context));
    }

    public function testCastHandlesVeryLargeNumericString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(\PHP_INT_MAX, $type->cast((string)\PHP_INT_MAX, $context));
    }

    public function testCastHandlesVerySmallNumericString(): void
    {
        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame(\PHP_INT_MIN, $type->cast((string)\PHP_INT_MIN, $context));
    }

    public function testCastPreservesPrecisionForLargeIntegers(): void
    {
        $largeInt = 9007199254740991; // 2^53 - 1 (max safe integer in JS, for comparison)

        $type = new IntType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame($largeInt, $type->cast($largeInt, $context));
        self::assertSame($largeInt, $type->cast((string)$largeInt, $context));
        self::assertSame($largeInt, $type->cast((float)$largeInt, $context));
    }
}
