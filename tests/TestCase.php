<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MetadataProviderFactory;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\Type\TypeNode;

/**
 * Every assertion of a conversion checks the predicate too: `isX()` must
 * agree with `X()` on the same value. It checks the built type and its copy
 * restored from the serialization.
 */
#[Group('unit'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase
{
    /**
     * Contexts of the repositories created by {@see createRepository()}
     *
     * @var \WeakMap<TypeRepository, Context>|null
     */
    private static ?\WeakMap $contexts = null;

    /**
     * @param list<TypeBuilderInterface<mixed>> $builders builders preceding
     *        the default ones
     * @param list<TypeDecoratorInterface> $decorators
     * @param list<ClassHookInterface> $hooks
     * @param Configuration|null $config configuration of the strictness in
     *        case of {@see null}
     * @param ClassAccessors|null $accessors accessors of the hooks in case
     *        of {@see null}
     */
    protected static function createRepository(
        bool $isStrict = false,
        array $builders = [],
        array $decorators = [],
        array $hooks = [],
        ?Configuration $config = null,
        ?ClassAccessors $accessors = null,
        ?MetadataProviderInterface $metadata = null,
    ): TypeRepository {
        $accessors ??= new ClassAccessors(hooks: $hooks);

        $repository = new TypeRepository(
            builders: [...$builders, ...Mapper::createDefaultBuilders()],
            metadata: $metadata ?? MetadataProviderFactory::create(),
            decorators: $decorators,
            accessors: $accessors,
        );

        self::$contexts ??= new \WeakMap();
        self::$contexts[$repository] = new Context(
            types: new ClassTypes($repository),
            config: $config ?? new Configuration(isStrict: $isStrict),
            accessors: $accessors,
        );

        return $repository;
    }

    /**
     * Returns the context of the conversion sharing the accessors with the
     * repository.
     */
    protected static function getContext(TypeRepository $repository): Context
    {
        return self::$contexts[$repository]
            ?? throw new \LogicException('The repository is not created by createRepository()');
    }

    /**
     * @param non-empty-string $type
     */
    protected static function parse(string $type): TypeNode
    {
        $node = (new TypeParser())->forType()->parse($type);

        self::assertInstanceOf(TypeNode::class, $node);

        return $node;
    }

    /**
     * @param non-empty-string $type
     * @return TypeInterface<mixed>
     */
    protected static function getType(string $type, ?TypeRepository $repository = null): TypeInterface
    {
        return ($repository ?? self::createRepository())->getType(self::parse($type));
    }

    /**
     * Returns the built type and its copy restored from the serialization,
     * every assertion of a conversion checks both.
     *
     * @param non-empty-string $type
     * @return array{TypeInterface<mixed>, TypeInterface<mixed>}
     */
    protected static function getTypeAndCopy(string $type, TypeRepository $repository): array
    {
        $instance = self::getType($type, $repository);
        $copy = \unserialize(\serialize($instance));

        self::assertInstanceOf(TypeInterface::class, $copy);

        return [$instance, $copy];
    }

    /**
     * @param non-empty-string $type
     */
    protected static function assertDenormalizes(
        string $type,
        mixed $input,
        mixed $expected,
        ?TypeRepository $repository = null,
    ): void {
        $repository ??= self::createRepository();

        foreach (self::getTypeAndCopy($type, $repository) as $instance) {
            self::assertTrue(
                $instance->isDenormalizable($input, self::getContext($repository)),
                'isDenormalizable() rejects the value denormalize() accepts',
            );
            self::assertResult($expected, $instance->denormalize($input, self::getContext($repository)));
        }
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message message of the error, with the path
     */
    protected static function assertDenormalizationFails(
        string $type,
        mixed $input,
        string $message,
        ?TypeRepository $repository = null,
    ): void {
        $repository ??= self::createRepository();

        foreach (self::getTypeAndCopy($type, $repository) as $instance) {
            self::assertFalse(
                $instance->isDenormalizable($input, self::getContext($repository)),
                'isDenormalizable() accepts the value denormalize() rejects',
            );

            $error = self::captureThrowable(
                static fn(): mixed => $instance->denormalize($input, self::getContext($repository)),
            );

            self::assertInstanceOf(InvalidValueException::class, $error);
            self::assertSame($message, $error->getMessage());
        }
    }

    /**
     * @param non-empty-string $type
     */
    protected static function assertNormalizes(
        string $type,
        mixed $value,
        mixed $expected,
        ?TypeRepository $repository = null,
    ): void {
        $repository ??= self::createRepository();

        foreach (self::getTypeAndCopy($type, $repository) as $instance) {
            self::assertTrue(
                $instance->isNormalizable($value, self::getContext($repository)),
                'isNormalizable() rejects the value normalize() accepts',
            );
            self::assertSame($expected, $instance->normalize($value, self::getContext($repository)));
        }
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message message of the error, with the path
     */
    protected static function assertNormalizationFails(
        string $type,
        mixed $value,
        string $message,
        ?TypeRepository $repository = null,
    ): void {
        $repository ??= self::createRepository();

        foreach (self::getTypeAndCopy($type, $repository) as $instance) {
            self::assertFalse(
                $instance->isNormalizable($value, self::getContext($repository)),
                'isNormalizable() accepts the value normalize() rejects',
            );

            $error = self::captureThrowable(
                static fn(): mixed => $instance->normalize($value, self::getContext($repository)),
            );

            self::assertInstanceOf(InvalidValueException::class, $error);
            self::assertSame($message, $error->getMessage());
        }
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
