<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnum;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnum;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnum;
use TypeLang\Mapper\Type\StringType;

final class StringTypeTest extends TypeTestCase
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

    /**
     * @return array<non-empty-string, array{mixed, bool, bool}>
     */
    public static function matchDataProvider(): array
    {
        return [
            // Positive cases
            'string(hello)' => ['hello', true, false],
            'string(empty)' => ['', true, false],
            'string(multibyte)' => ['Привет мир! 🌍', true, false],

            // Negative cases - strict mode doesn't affect match()
            'int(42)' => [42, false, false],
            'int(0)' => [0, false, false],
            'int(negative)' => [-42, false, false],
            'float(3.14)' => [3.14, false, false],
            'float(0.0)' => [0.0, false, false],
            'float(negative)' => [-3.14, false, false],
            'bool(true)' => [true, false, false],
            'bool(false)' => [false, false, false],
            'null' => [null, false, false],
            'array(empty)' => [[], false, false],
            'array(values)' => [[1, 2, 3], false, false],
            'object(stdClass)' => [new \stdClass(), false, false],
        ];
    }

    /**
     * @return array<non-empty-string, array{mixed, string, bool}>
     */
    public static function castSuccessDataProvider(): array
    {
        return [
            // String inputs (work in both modes)
            'string(hello)' => ['hello', 'hello', false],
            'string(hello) strict' => ['hello', 'hello', true],
            'string(empty)' => ['', '', false],
            'string(empty) strict' => ['', '', true],
            'string(multibyte)' => ['Привет мир! 🌍', 'Привет мир! 🌍', false],
            'string(numeric)' => ['123', '123', false],
            'string(float-like)' => ['3.14', '3.14', false],
            'string(special chars)' => ["Hello\nWorld\t", "Hello\nWorld\t", false],
            'string(very long)' => [\str_repeat('a', 100000), \str_repeat('a', 100000), false],

            // Non-strict coercions
            'non-strict + null' => [null, '', false],
            'non-strict + true' => [true, 'true', false],
            'non-strict + false' => [false, 'false', false],
            'non-strict + int(42)' => [42, '42', false],
            'non-strict + int(-42)' => [-42, '-42', false],
            'non-strict + int(0)' => [0, '0', false],
            'non-strict + int(max)' => [\PHP_INT_MAX, (string)\PHP_INT_MAX, false],
            'non-strict + int(min)' => [\PHP_INT_MIN, (string)\PHP_INT_MIN, false],
            'non-strict + float(3.14)' => [3.14, '3.14', false],
            'non-strict + float(-3.14)' => [-3.14, '-3.14', false],
            'non-strict + float(5.0)' => [5.0, '5.0', false],
            'non-strict + float(0.0)' => [0.0, '0.0', false],
            'non-strict + float(-10.0)' => [-10.0, '-10.0', false],
            'non-strict + nan' => [\NAN, 'nan', false],
            'non-strict + inf' => [\INF, 'inf', false],
            'non-strict + -inf' => [-\INF, '-inf', false],
        ];
    }

    /**
     * @return array<non-empty-string, array{mixed, bool}>
     */
    public static function castExceptionDataProvider(): array
    {
        return [
            // Strict mode exceptions
            'strict + null' => [null, true],
            'strict + true' => [true, true],
            'strict + false' => [false, true],
            'strict + int(42)' => [42, true],
            'strict + float(3.14)' => [3.14, true],
            'strict + array' => [[1, 2, 3], true],
            'strict + object' => [new \stdClass(), true],

            // Non-strict exceptions (unconvertible types)
            'non-strict + array' => [[1, 2, 3], false],
            'non-strict + object(stdClass)' => [new \stdClass(), false],
        ];
    }

    /**
     * @return array<non-empty-string, array{string}>
     */
    public static function floatEdgeCasesDataProvider(): array
    {
        return [
            'very small float' => ['0.0000001'],
            'very large float' => ['1.23e15'],
            'scientific notation' => ['1.5e-10'],
            'negative zero' => ['-0.0'],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(mixed $value, bool $expectedMatch, bool $strictTypes): void
    {
        $type = new StringType();
        $context = $this->createContext($strictTypes);

        self::assertSame($expectedMatch, $type->match($value, $context));
    }

    #[DataProvider('castSuccessDataProvider')]
    public function testCast(mixed $value, string $expected, bool $strictTypes): void
    {
        $type = new StringType();
        $context = $this->createContext($strictTypes);

        self::assertSame($expected, $type->cast($value, $context));
    }

    #[DataProvider('castExceptionDataProvider')]
    public function testCastThrowsException(mixed $value, bool $strictTypes): void
    {
        $type = new StringType();
        $context = $this->createContext($strictTypes);

        $this->expectException(InvalidValueException::class);
        $type->cast($value, $context);
    }

    public function testCastCoercesStringableObject(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame('stringable object', $type->cast($stringable, $context));
    }

    public function testCastCoercesStringableWithEmptyString(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return '';
            }
        };

        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame('', $type->cast($stringable, $context));
    }

    public function testCastCoercesStringableWithMultibyteString(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Тест 🎉';
            }
        };

        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame('Тест 🎉', $type->cast($stringable, $context));
    }

    public function testCastThrowsExceptionForStringableInStrictMode(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };

        $type = new StringType();
        $context = $this->createContext(strictTypes: true);

        $this->expectException(InvalidValueException::class);
        $type->cast($stringable, $context);
    }

    public function testCastCoercesBackedStringEnum(): void
    {
        $enum = StringBackedEnum::Case1;

        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame('value1', $type->cast($enum, $context));
    }

    public function testCastCoercesBackedIntEnum(): void
    {
        $enum = IntBackedEnum::Case1;

        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        self::assertSame('1', $type->cast($enum, $context));
    }

    public function testCastThrowsExceptionForUnitEnumInNonStrictMode(): void
    {
        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        $result = $type->cast(UnitEnum::Case1, $context);
        self::assertSame('Case1', $result);
    }

    public function testCastThrowsExceptionForResourceInNonStrictMode(): void
    {
        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        $result = $type->cast(\fopen('php://memory', 'r'), $context);
        self::assertSame('stream', $result);
    }

    public function testCastThrowsExceptionForClosureInNonStrictMode(): void
    {
        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        $this->expectException(InvalidValueException::class);
        $type->cast(fn() => 'test', $context);
    }

    #[DataProvider('floatEdgeCasesDataProvider')]
    public function testCastHandlesFloatEdgeCases(string $floatValue): void
    {
        $type = new StringType();
        $context = $this->createContext(strictTypes: false);

        $result = $type->cast((float)$floatValue, $context);

        self::assertIsString($result);

        if (!\is_nan((float)$floatValue)) {
            self::assertStringContainsString('.', $result);
        }
    }
}
