<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;

/**
 * Every assertion of a conversion checks the predicate too: `isX()` must
 * agree with `X()` on the same value.
 */
#[Group('unit'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase
{
    /**
     * @param list<TypeBuilderInterface<mixed>> $builders
     * @param list<TypeDecoratorInterface> $decorators
     * @param list<ClassHookInterface> $hooks
     * @param list<AccessStrategyInterface> $accessors
     * @param list<InstantiationStrategyInterface> $instantiators
     * @param Configuration|null $config configuration of the strictness in
     *        case of {@see null}
     */
    protected static function createMapper(
        bool $isStrict = false,
        array $builders = [],
        array $decorators = [],
        array $hooks = [],
        ?Configuration $config = null,
        array $accessors = [],
        array $instantiators = [],
        ?MetadataProviderInterface $metadata = null,
    ): Mapper {
        return new Mapper(
            config: $config ?? new Configuration(isStrict: $isStrict),
            builders: $builders,
            hooks: $hooks,
            decorators: $decorators,
            accessors: $accessors,
            instantiators: $instantiators,
            metadata: $metadata,
        );
    }

    /**
     * Returns the error of building the type, the type is built by a check
     * of a value.
     *
     * @param non-empty-string $type
     */
    protected static function captureBuildError(string $type, ?Mapper $mapper = null): \Throwable
    {
        $mapper ??= self::createMapper();

        return self::captureThrowable(static fn(): bool => $mapper->isNormalizable(null, $type));
    }

    /**
     * @param non-empty-string $type
     */
    protected static function assertDenormalizes(
        string $type,
        mixed $input,
        mixed $expected,
        ?Mapper $mapper = null,
    ): void {
        $mapper ??= self::createMapper();

        self::assertTrue(
            $mapper->isDenormalizable($input, $type),
            'isDenormalizable() rejects the value denormalize() accepts',
        );
        self::assertResult($expected, $mapper->denormalize($input, $type));
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message message of the error, with the path
     */
    protected static function assertDenormalizationFails(
        string $type,
        mixed $input,
        string $message,
        ?Mapper $mapper = null,
    ): void {
        $mapper ??= self::createMapper();

        self::assertFalse(
            $mapper->isDenormalizable($input, $type),
            'isDenormalizable() accepts the value denormalize() rejects',
        );

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize($input, $type));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame($message, $error->getMessage());
    }

    /**
     * @param non-empty-string $type
     */
    protected static function assertNormalizes(
        string $type,
        mixed $value,
        mixed $expected,
        ?Mapper $mapper = null,
    ): void {
        $mapper ??= self::createMapper();

        self::assertTrue(
            $mapper->isNormalizable($value, $type),
            'isNormalizable() rejects the value normalize() accepts',
        );
        self::assertSame($expected, $mapper->normalize($value, $type));
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message message of the error, with the path
     */
    protected static function assertNormalizationFails(
        string $type,
        mixed $value,
        string $message,
        ?Mapper $mapper = null,
    ): void {
        $mapper ??= self::createMapper();

        self::assertFalse(
            $mapper->isNormalizable($value, $type),
            'isNormalizable() accepts the value normalize() rejects',
        );

        $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($value, $type));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame($message, $error->getMessage());
    }

    /**
     * @param callable(): mixed $fn
     */
    protected static function captureThrowable(callable $fn): \Throwable
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            return $e;
        }

        self::fail('Expected a throwable to be thrown, but none was');
    }

    /**
     * Objects are compared by their state, arrays by their keys and
     * elements, enum cases and other values by identity.
     */
    private static function assertResult(mixed $expected, mixed $actual): void
    {
        if (\is_array($expected)) {
            self::assertIsArray($actual);
            self::assertSame(\array_keys($expected), \array_keys($actual));

            foreach ($expected as $key => $item) {
                self::assertResult($item, $actual[$key]);
            }

            return;
        }

        if (\is_object($expected) && !$expected instanceof \UnitEnum) {
            self::assertEquals($expected, $actual);

            return;
        }

        self::assertSame($expected, $actual);
    }
}
