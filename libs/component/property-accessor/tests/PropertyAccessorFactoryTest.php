<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotFoundException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\DirectPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\RawPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\ReflectionPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\DirectPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\RawPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\ReflectionPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\AsymmetricStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\BaseStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\HookStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\InheritedStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\ReadonlyStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\StaticStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\VisibilityStub;

final class PropertyAccessorFactoryTest extends TestCase
{
    public function testStrategyOfUserIsTriedBeforeDefaultOnes(): void
    {
        $reader = new DirectPropertyReader('publicProperty');
        $factory = PropertyAccessorFactory::createDefault([self::createStrategy($reader)]);

        $property = new \ReflectionProperty(VisibilityStub::class, 'privateProperty');

        self::assertSame($reader, $factory->createReader($property, new PropertyMetadata('privateProperty')));
    }

    public function testPropertyNotTakenByStrategyOfUserIsAccessedByDefaultOnes(): void
    {
        $factory = PropertyAccessorFactory::createDefault([self::createStrategy(null)]);

        $property = new \ReflectionProperty(VisibilityStub::class, 'privateProperty');

        self::assertInstanceOf(
            ReflectionPropertyReader::class,
            $factory->createReader($property, new PropertyMetadata('privateProperty')),
        );
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testStrategyOfUserReadsVirtualPropertyWithoutGetHook(): void
    {
        $reader = new DirectPropertyReader('virtualWriteOnly');
        $factory = PropertyAccessorFactory::createDefault([self::createStrategy($reader)]);

        $property = new \ReflectionProperty(HookStub::class, 'virtualWriteOnly');

        self::assertSame($reader, $factory->createReader($property, new PropertyMetadata('virtualWriteOnly')));
    }

    public function testWritesPublicPropertyDirectly(): void
    {
        $object = new VisibilityStub();
        $writer = self::createWriter(VisibilityStub::class, 'publicProperty');

        $writer->write($object, 'written');

        self::assertInstanceOf(DirectPropertyWriter::class, $writer);
        self::assertSame('written', $object->publicProperty);
    }

    public function testWritesPrivatePropertyByReflection(): void
    {
        $object = new VisibilityStub();
        $writer = self::createWriter(VisibilityStub::class, 'privateProperty');

        $writer->write($object, 'written');

        self::assertInstanceOf(ReflectionPropertyWriter::class, $writer);
        self::assertSame('written', $object->getPrivateProperty());
    }

    public function testWritesPrivatePropertyOfParentClass(): void
    {
        $object = new InheritedStub();

        self::createWriter(BaseStub::class, 'basePrivate')->write($object, 'written');

        self::assertSame('written', $object->getBasePrivate());
    }

    public function testInitializesReadonlyPropertyOutsideClass(): void
    {
        $object = (new \ReflectionClass(ReadonlyStub::class))->newInstanceWithoutConstructor();
        $writer = self::createWriter(ReadonlyStub::class, 'readonlyProperty');

        $writer->write($object, 'written');

        self::assertInstanceOf(ReflectionPropertyWriter::class, $writer);
        self::assertSame('written', $object->readonlyProperty);
    }

    public function testRewritingInitializedReadonlyPropertyFails(): void
    {
        $writer = self::createWriter(ReadonlyStub::class, 'readonlyProperty');

        $this->expectException(\Error::class);

        $writer->write(new ReadonlyStub(), 'written');
    }

    public function testValueOfWrongTypeIsNotConvertedForPublicProperty(): void
    {
        $writer = self::createWriter(VisibilityStub::class, 'publicProperty');

        $this->expectException(\TypeError::class);

        $writer->write(new VisibilityStub(), 42);
    }

    public function testWriterIsSharedBetweenObjects(): void
    {
        $first = new VisibilityStub();
        $second = new VisibilityStub();
        $writer = self::createWriter(VisibilityStub::class, 'privateProperty');

        $writer->write($first, 'first');
        $writer->write($second, 'second');

        self::assertSame('first', $first->getPrivateProperty());
        self::assertSame('second', $second->getPrivateProperty());
    }

    public function testReadsPublicPropertyDirectly(): void
    {
        $reader = self::createReader(VisibilityStub::class, 'publicProperty');

        self::assertInstanceOf(DirectPropertyReader::class, $reader);
        self::assertSame('public-value', $reader->read(new VisibilityStub()));
    }

    public function testReadsPrivatePropertyByReflection(): void
    {
        $reader = self::createReader(VisibilityStub::class, 'privateProperty');

        self::assertInstanceOf(ReflectionPropertyReader::class, $reader);
        self::assertSame('private-value', $reader->read(new VisibilityStub()));
    }

    public function testReadsPrivatePropertyOfParentClass(): void
    {
        $reader = self::createReader(BaseStub::class, 'basePrivate');

        self::assertSame('base-private', $reader->read(new InheritedStub()));
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string}>
     */
    public static function staticPropertyDataProvider(): iterable
    {
        yield 'public' => ['publicStatic'];
        yield 'protected' => ['protectedStatic'];
        yield 'private' => ['privateStatic'];
    }

    /**
     * @param non-empty-string $name
     */
    #[DataProvider('staticPropertyDataProvider')]
    public function testStaticPropertyIsNotWritten(string $name): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->expectExceptionMessage(\sprintf('Property "%s::$%s" is static', StaticStub::class, $name));

        self::createWriter(StaticStub::class, $name);
    }

    /**
     * @param non-empty-string $name
     */
    #[DataProvider('staticPropertyDataProvider')]
    public function testStaticPropertyIsNotRead(string $name): void
    {
        $this->expectException(PropertyNotFoundException::class);

        self::createReader(StaticStub::class, $name);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testReadsPropertyWithGetHookBypassingHook(): void
    {
        $reader = self::createReader(HookStub::class, 'hooked');

        self::assertInstanceOf(RawPropertyReader::class, $reader);
        self::assertSame('raw-init', $reader->read(new HookStub()));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testReadsVirtualPropertyThroughGetHook(): void
    {
        $reader = self::createReader(HookStub::class, 'virtualReadOnly');

        self::assertInstanceOf(DirectPropertyReader::class, $reader);
        self::assertSame('computed', $reader->read(new HookStub()));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testVirtualPropertyWithoutGetHookIsNotRead(): void
    {
        $this->expectException(PropertyNotReadableException::class);
        $this->expectExceptionMessage(\sprintf('Property "%s::$virtualWriteOnly" is write-only', HookStub::class));

        self::createReader(HookStub::class, 'virtualWriteOnly');
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testWritesPropertyWithSetHookBypassingHook(): void
    {
        $object = new HookStub();
        $property = new \ReflectionProperty(HookStub::class, 'hooked');
        $writer = PropertyAccessorFactory::createDefault()->createWriter($property, new PropertyMetadata($property->name));

        $writer->write($object, 'written');

        self::assertInstanceOf(RawPropertyWriter::class, $writer);
        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        self::assertSame('written', $property->getRawValue($object));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testVirtualPropertyWithoutSetHookIsNotWritten(): void
    {
        $this->expectException(PropertyNotWritableException::class);
        $this->expectExceptionMessage(\sprintf('Property "%s::$virtualReadOnly" is read-only', HookStub::class));

        self::createWriter(HookStub::class, 'virtualReadOnly');
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testExceptionOfSetHookIsThrownAsIs(): void
    {
        $writer = self::createWriter(HookStub::class, 'virtualWriteOnly');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Rejected x');

        $writer->write(new HookStub(), 'x');
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string}>
     */
    public static function asymmetricPropertyDataProvider(): iterable
    {
        yield 'private(set)' => ['privateSet'];
        yield 'protected(set)' => ['protectedSet'];
    }

    /**
     * @param non-empty-string $name
     */
    #[RequiresPhp('>= 8.4.0')]
    #[DataProvider('asymmetricPropertyDataProvider')]
    public function testWritesPropertyWithRestrictedSetByReflection(string $name): void
    {
        $object = new AsymmetricStub();
        $writer = self::createWriter(AsymmetricStub::class, $name);

        $writer->write($object, 'written');

        self::assertInstanceOf(ReflectionPropertyWriter::class, $writer);
        self::assertSame('written', $object->{$name});
    }

    /**
     * @param non-empty-string $name
     */
    #[RequiresPhp('>= 8.4.0')]
    #[DataProvider('asymmetricPropertyDataProvider')]
    public function testReadsPropertyWithRestrictedSetDirectly(string $name): void
    {
        self::assertInstanceOf(DirectPropertyReader::class, self::createReader(AsymmetricStub::class, $name));
    }

    /**
     * @param class-string $class
     * @param non-empty-string $name
     * @return PropertyWriterInterface<object, mixed>
     */
    private static function createWriter(string $class, string $name): PropertyWriterInterface
    {
        return PropertyAccessorFactory::createDefault()
            ->createWriter(new \ReflectionProperty($class, $name), new PropertyMetadata($name));
    }

    /**
     * @param class-string $class
     * @param non-empty-string $name
     * @return PropertyReaderInterface<object, mixed>
     */
    private static function createReader(string $class, string $name): PropertyReaderInterface
    {
        return PropertyAccessorFactory::createDefault()
            ->createReader(new \ReflectionProperty($class, $name), new PropertyMetadata($name));
    }

    /**
     * @param PropertyReaderInterface<object, mixed>|null $reader
     */
    private static function createStrategy(?PropertyReaderInterface $reader): AccessStrategyInterface
    {
        return new class ($reader) implements AccessStrategyInterface {
            /**
             * @param PropertyReaderInterface<object, mixed>|null $reader
             */
            public function __construct(
                private readonly ?PropertyReaderInterface $reader,
            ) {}

            public function tryCreateReader(
                \ReflectionProperty $property,
                PropertyMetadata $metadata,
            ): ?PropertyReaderInterface {
                return $this->reader;
            }

            public function tryCreateWriter(
                \ReflectionProperty $property,
                PropertyMetadata $metadata,
            ): ?PropertyWriterInterface {
                return null;
            }
        };
    }
}
