<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\AccessNotSupportedException;
use TypeLang\Mapper\PropertyAccessor\Exception\MethodNotCallableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotFoundException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\DirectPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\MethodPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\RawPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\ReflectionPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\DirectPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\MethodPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\RawPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\ReflectionPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\AsymmetricStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\BaseStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\HookStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\InheritedStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\MethodStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\ReadonlyInheritedStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\ReadonlyStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\StaticStub;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\VisibilityStub;

final class PropertyAccessorFactoryTest extends TestCase
{
    public function testStrategyOfUserIsTriedBeforeDefaultOnes(): void
    {
        $reader = new DirectPropertyReader('publicProperty');
        $factory = PropertyAccessorFactory::createDefault([self::createStrategy($reader)]);

        self::assertSame($reader, $factory->createReader(
            new \ReflectionClass(VisibilityStub::class),
            new OutputFieldMetadata('privateProperty', new SlotAccessMetadata('privateProperty')),
        ));
    }

    public function testAccessNotTakenByStrategyOfUserIsExecutedByDefaultOnes(): void
    {
        $factory = PropertyAccessorFactory::createDefault([self::createStrategy(null)]);

        self::assertInstanceOf(ReflectionPropertyReader::class, $factory->createReader(
            new \ReflectionClass(VisibilityStub::class),
            new OutputFieldMetadata('privateProperty', new SlotAccessMetadata('privateProperty')),
        ));
    }

    public function testStrategyOfUserTakesItsOwnAccess(): void
    {
        $reader = new DirectPropertyReader('publicProperty');
        $factory = PropertyAccessorFactory::createDefault([self::createStrategy($reader)]);
        $access = new class extends AccessMetadata {};

        self::assertSame($reader, $factory->createReader(
            new \ReflectionClass(VisibilityStub::class),
            new OutputFieldMetadata('virtual', $access),
        ));
    }

    public function testAccessNotTakenByAnyStrategyIsNotRead(): void
    {
        $factory = PropertyAccessorFactory::createDefault();
        $access = new class extends AccessMetadata {};

        $this->expectException(AccessNotSupportedException::class);
        $this->expectExceptionMessage(\sprintf(
            'No access strategy reads field "virtual" of "%s"',
            VisibilityStub::class,
        ));

        $factory->createReader(
            new \ReflectionClass(VisibilityStub::class),
            new OutputFieldMetadata('virtual', $access),
        );
    }

    public function testAccessNotTakenByAnyStrategyIsNotWritten(): void
    {
        $factory = new PropertyAccessorFactory([self::createStrategy(null)]);

        $this->expectException(AccessNotSupportedException::class);
        $this->expectExceptionMessage(\sprintf(
            'No access strategy writes field "publicProperty" of "%s"',
            VisibilityStub::class,
        ));

        $factory->createWriter(
            new \ReflectionClass(VisibilityStub::class),
            new InputFieldMetadata('publicProperty', new SlotAccessMetadata('publicProperty')),
        );
    }

    public function testMissingPropertyOfAccessIsNotFound(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->expectExceptionMessage(\sprintf('Property "%s::$missing" does not exist', VisibilityStub::class));

        self::createReader(VisibilityStub::class, 'missing');
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

    public function testInitializesReadonlyPropertyOfParentClass(): void
    {
        $object = (new \ReflectionClass(ReadonlyInheritedStub::class))->newInstanceWithoutConstructor();

        self::createWriter(ReadonlyInheritedStub::class, 'promoted')->write($object, 'written');

        self::assertSame('written', $object->promoted);
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
    public function testReadsStorageOfPropertyWithGetHookBypassingHook(): void
    {
        $reader = self::createReader(HookStub::class, 'hooked');

        self::assertInstanceOf(RawPropertyReader::class, $reader);
        self::assertSame('raw-init', $reader->read(new HookStub()));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testWritesStorageOfPropertyWithSetHookBypassingHook(): void
    {
        $object = new HookStub();
        $writer = self::createWriter(HookStub::class, 'hooked');

        $writer->write($object, 'written');

        self::assertInstanceOf(RawPropertyWriter::class, $writer);
        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        self::assertSame('written', (new \ReflectionProperty(HookStub::class, 'hooked'))->getRawValue($object));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testStorageOfVirtualPropertyIsNotRead(): void
    {
        $this->expectException(PropertyNotReadableException::class);
        $this->expectExceptionMessage(\sprintf(
            'Property "%s::$virtualReadOnly" has no backing store to read',
            HookStub::class,
        ));

        self::createReader(HookStub::class, 'virtualReadOnly');
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testStorageOfVirtualPropertyIsNotWritten(): void
    {
        $this->expectException(PropertyNotWritableException::class);
        $this->expectExceptionMessage(\sprintf(
            'Property "%s::$virtualWriteOnly" has no backing store to write',
            HookStub::class,
        ));

        self::createWriter(HookStub::class, 'virtualWriteOnly');
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testReadsThroughGetHook(): void
    {
        $reader = self::createReader(HookStub::class, 'hooked', new HookAccessMetadata('hooked'));

        self::assertInstanceOf(DirectPropertyReader::class, $reader);
        self::assertSame('via-get-hook', $reader->read(new HookStub()));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testReadsVirtualPropertyThroughGetHook(): void
    {
        $reader = self::createReader(HookStub::class, 'virtualReadOnly', new HookAccessMetadata('virtualReadOnly'));

        self::assertSame('computed', $reader->read(new HookStub()));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testWritesThroughSetHook(): void
    {
        $object = new HookStub();
        $writer = self::createWriter(HookStub::class, 'hooked', new HookAccessMetadata('hooked'));

        $writer->write($object, 'x');

        self::assertInstanceOf(DirectPropertyWriter::class, $writer);
        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        self::assertSame('via-set-hook:x', (new \ReflectionProperty(HookStub::class, 'hooked'))->getRawValue($object));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testHookAccessWithoutGetHookIsNotRead(): void
    {
        $this->expectException(PropertyNotReadableException::class);
        $this->expectExceptionMessage(\sprintf('Property "%s::$virtualWriteOnly" has no get hook', HookStub::class));

        self::createReader(HookStub::class, 'virtualWriteOnly', new HookAccessMetadata('virtualWriteOnly'));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testHookAccessWithoutSetHookIsNotWritten(): void
    {
        $this->expectException(PropertyNotWritableException::class);
        $this->expectExceptionMessage(\sprintf('Property "%s::$virtualReadOnly" has no set hook', HookStub::class));

        self::createWriter(HookStub::class, 'virtualReadOnly', new HookAccessMetadata('virtualReadOnly'));
    }

    public function testHookAccessOfPropertyWithoutHooksIsNotRead(): void
    {
        $this->expectException(PropertyNotReadableException::class);

        self::createReader(VisibilityStub::class, 'publicProperty', new HookAccessMetadata('publicProperty'));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testExceptionOfSetHookIsThrownAsIs(): void
    {
        $writer = self::createWriter(HookStub::class, 'virtualWriteOnly', new HookAccessMetadata('virtualWriteOnly'));

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

    public function testReadsThroughGetter(): void
    {
        $reader = self::createReader(MethodStub::class, 'value', new MethodAccessMetadata('getValue'));

        self::assertInstanceOf(MethodPropertyReader::class, $reader);
        self::assertSame('initial', $reader->read(new MethodStub()));
    }

    public function testReadsThroughGetterWithOptionalParameter(): void
    {
        $reader = self::createReader(MethodStub::class, 'value', new MethodAccessMetadata('withOptional'));

        self::assertSame('default', $reader->read(new MethodStub()));
    }

    public function testWritesThroughSetterIgnoringResult(): void
    {
        $object = new MethodStub();
        $writer = self::createWriter(MethodStub::class, 'value', new MethodAccessMetadata('setValue'));

        $writer->write($object, 'x');

        self::assertInstanceOf(MethodPropertyWriter::class, $writer);
        self::assertSame('set:x', $object->getValue());
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, non-empty-string}>
     */
    public static function invalidGetterDataProvider(): iterable
    {
        yield 'missing' => ['missing', 'does not exist'];
        yield 'not public' => ['hidden', 'is not public'];
        yield 'static' => ['create', 'is static'];
        yield 'required parameter' => ['find', 'requires arguments, a getter takes none'];
    }

    /**
     * @param non-empty-string $method
     */
    #[DataProvider('invalidGetterDataProvider')]
    public function testInvalidGetterIsNotCalled(string $method, string $reason): void
    {
        $this->expectException(MethodNotCallableException::class);
        $this->expectExceptionMessage(\sprintf(
            'Method "%s::%s()" of field "value" %s',
            MethodStub::class,
            $method,
            $reason,
        ));

        self::createReader(MethodStub::class, 'value', new MethodAccessMetadata($method));
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, non-empty-string}>
     */
    public static function invalidSetterDataProvider(): iterable
    {
        yield 'missing' => ['missing', 'does not exist'];
        yield 'no parameters' => ['getValue', 'must take exactly one value as a setter'];
        yield 'two required parameters' => ['replace', 'must take exactly one value as a setter'];
        yield 'variadic parameter' => ['tag', 'must take exactly one value as a setter'];
    }

    /**
     * @param non-empty-string $method
     */
    #[DataProvider('invalidSetterDataProvider')]
    public function testInvalidSetterIsNotCalled(string $method, string $reason): void
    {
        $this->expectException(MethodNotCallableException::class);
        $this->expectExceptionMessage(\sprintf(
            'Method "%s::%s()" of field "value" %s',
            MethodStub::class,
            $method,
            $reason,
        ));

        self::createWriter(MethodStub::class, 'value', new MethodAccessMetadata($method));
    }

    /**
     * @param class-string $class
     * @param non-empty-string $name
     * @return PropertyWriterInterface<object, mixed>
     */
    private static function createWriter(
        string $class,
        string $name,
        ?AccessMetadata $access = null,
    ): PropertyWriterInterface {
        return PropertyAccessorFactory::createDefault()->createWriter(
            new \ReflectionClass($class),
            new InputFieldMetadata($name, $access ?? new SlotAccessMetadata($name)),
        );
    }

    /**
     * @param class-string $class
     * @param non-empty-string $name
     * @return PropertyReaderInterface<object, mixed>
     */
    private static function createReader(
        string $class,
        string $name,
        ?AccessMetadata $access = null,
    ): PropertyReaderInterface {
        return PropertyAccessorFactory::createDefault()->createReader(
            new \ReflectionClass($class),
            new OutputFieldMetadata($name, $access ?? new SlotAccessMetadata($name)),
        );
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
                \ReflectionClass $class,
                OutputFieldMetadata $field,
            ): ?PropertyReaderInterface {
                return $this->reader;
            }

            public function tryCreateWriter(
                \ReflectionClass $class,
                InputFieldMetadata $field,
            ): ?PropertyWriterInterface {
                return null;
            }
        };
    }
}
