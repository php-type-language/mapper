<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Instantiator\Tests\Stub\AbstractStub;
use TypeLang\Mapper\Instantiator\Tests\Stub\ConstructorStub;
use TypeLang\Mapper\Instantiator\Tests\Stub\EmptyStub;
use TypeLang\Mapper\Instantiator\Tests\Stub\EnumStub;
use TypeLang\Mapper\Instantiator\Tests\Stub\InterfaceStub;
use TypeLang\Mapper\Instantiator\Tests\Stub\TraitStub;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

final class InstantiatorFactoryTest extends TestCase
{
    public function testStrategyOfUserCreatesObjectsOfAbstractClass(): void
    {
        $object = AbstractStub::create();

        $strategy = new class ($object) implements InstantiationStrategyInterface {
            public function __construct(
                private readonly AbstractStub $object,
            ) {}

            public function tryCreateInstantiator(
                \ReflectionClass $class,
                ClassMetadata $metadata,
            ): ?InstantiatorInterface {
                if ($class->name !== AbstractStub::class) {
                    return null;
                }

                return new class ($this->object) implements InstantiatorInterface {
                    public function __construct(
                        private readonly AbstractStub $object,
                    ) {}

                    public function instantiate(array $data = []): object
                    {
                        return $this->object;
                    }
                };
            }
        };

        $instantiator = InstantiatorFactory::createDefault([$strategy])
            ->createInstantiator(new \ReflectionClass(AbstractStub::class), new ClassMetadata(AbstractStub::class));

        self::assertSame($object, $instantiator->instantiate([]));
    }

    public function testClassNotTakenByStrategyOfUserIsCreatedByDefaultOne(): void
    {
        $strategy = new class implements InstantiationStrategyInterface {
            public function tryCreateInstantiator(
                \ReflectionClass $class,
                ClassMetadata $metadata,
            ): ?InstantiatorInterface {
                return null;
            }
        };

        $object = InstantiatorFactory::createDefault([$strategy])
            ->createInstantiator(new \ReflectionClass(EmptyStub::class), new ClassMetadata(EmptyStub::class))
            ->instantiate([]);

        self::assertInstanceOf(EmptyStub::class, $object);
    }

    public function testDoesNotCallConstructor(): void
    {
        $object = self::createInstantiator(ConstructorStub::class)->instantiate([]);

        self::assertInstanceOf(ConstructorStub::class, $object);
        self::assertFalse($object->isConstructed);
    }

    public function testInitializesDefaultPropertyValues(): void
    {
        self::assertSame('default', self::createInstantiator(EmptyStub::class)->instantiate([])->value);
    }

    public function testCreatesDistinctObjects(): void
    {
        $instantiator = self::createInstantiator(EmptyStub::class);

        $first = $instantiator->instantiate([]);
        $first->value = 'changed';

        $second = $instantiator->instantiate([]);

        self::assertNotSame($first, $second);
        self::assertSame('default', $second->value);
    }

    public function testCreatesInternalClassThatNeedsNoConstructor(): void
    {
        self::assertInstanceOf(\stdClass::class, self::createInstantiator(\stdClass::class)->instantiate([]));
    }

    /**
     * @return iterable<non-empty-string, array{class-string, non-empty-string}>
     */
    public static function notInstantiableDataProvider(): iterable
    {
        yield 'abstract class' => [AbstractStub::class, 'Cannot instantiate abstract class "%s"'];
        yield 'interface' => [InterfaceStub::class, 'Cannot instantiate interface "%s"'];
        yield 'trait' => [TraitStub::class, 'Cannot instantiate trait "%s"'];
        yield 'enum' => [EnumStub::class, 'Cannot instantiate enum "%s"'];
        yield 'internal final class' => [\Closure::class, 'Cannot instantiate class "%s" without the constructor'];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('notInstantiableDataProvider')]
    public function testClassIsRejectedOnCreationOfInstantiator(string $class, string $message): void
    {
        $error = self::captureThrowable(static fn(): object => self::createInstantiator($class));

        self::assertInstanceOf(ClassNotInstantiableException::class, $error);
        self::assertSame($class, $error->class);
        self::assertSame(\sprintf($message, $class), $error->getMessage());
    }

    public function testClassNotTakenByAnyStrategyIsRejected(): void
    {
        $factory = new InstantiatorFactory([new class implements InstantiationStrategyInterface {
            public function tryCreateInstantiator(
                \ReflectionClass $class,
                ClassMetadata $metadata,
            ): ?InstantiatorInterface {
                return null;
            }
        }]);

        $error = self::captureThrowable(static fn(): object => $factory->createInstantiator(
            new \ReflectionClass(EmptyStub::class),
            new ClassMetadata(EmptyStub::class),
        ));

        self::assertInstanceOf(ClassNotInstantiableException::class, $error);
        self::assertSame(
            \sprintf('No instantiation strategy takes class "%s"', EmptyStub::class),
            $error->getMessage(),
        );
    }

    /**
     * @template TArgObject of object
     * @param class-string<TArgObject> $class
     * @return InstantiatorInterface<TArgObject>
     */
    private static function createInstantiator(string $class): InstantiatorInterface
    {
        return InstantiatorFactory::createDefault()
            ->createInstantiator(new \ReflectionClass($class), new ClassMetadata($class));
    }
}
