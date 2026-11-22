<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\EnumDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ObjectDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\VirtualHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\WiderSetHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php85\ClosureDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PromotedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ThrowingDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitUserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class ReflectionMetadataReaderTest extends TestCase
{
    public function testNativeTypesAreRead(): void
    {
        $metadata = self::readReflection(UserStub::class);

        $id = self::getProperty($metadata, 'id');
        self::assertType('int', $id->read?->type);
        self::assertType('int', $id->write?->type);
        self::assertType('?string', self::getProperty($metadata, 'email')->read?->type);
        self::assertNull(self::getProperty($metadata, 'untyped')->read?->type);
    }

    public function testPropertyAccessesStorageInBothDirections(): void
    {
        $id = self::getProperty(self::readReflection(UserStub::class), 'id');

        self::assertEquals(new StorageAccessPoint('id'), $id->read?->point);
        self::assertEquals(new StorageAccessPoint('id'), $id->write?->point);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testVirtualPropertyAccessesHooksItHas(): void
    {
        $metadata = self::readReflection(VirtualHookStub::class);

        $computed = self::getProperty($metadata, 'computed');
        self::assertEquals(new HookAccessPoint('computed'), $computed->read?->point);
        self::assertNull($computed->write);

        $alias = self::getProperty($metadata, 'alias');
        self::assertNull($alias->read);
        self::assertEquals(new HookAccessPoint('alias'), $alias->write?->point);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testPropertyWithStorageAndHookAccessesStorage(): void
    {
        $backed = self::getProperty(self::readReflection(VirtualHookStub::class), 'backed');

        self::assertEquals(new StorageAccessPoint('backed'), $backed->read?->point);
        self::assertEquals(new StorageAccessPoint('backed'), $backed->write?->point);
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string}>
     */
    public static function widerSetHookDataProvider(): iterable
    {
        yield 'array|string' => ['list'];
        yield 'int|string' => ['number'];
        yield 'string|\Stringable' => ['id'];
    }

    /**
     * @param non-empty-string $name
     */
    #[RequiresPhp('>= 8.4.0')]
    #[DataProvider('widerSetHookDataProvider')]
    public function testWriteTypeOfPropertyWithStorageIsItsOwnType(string $name): void
    {
        $property = self::getProperty(self::readReflection(WiderSetHookStub::class), $name);

        self::assertType('string', $property->read?->type);
        self::assertType('string', $property->write?->type);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testWriteTypeOfVirtualPropertyIsParameterOfSetHook(): void
    {
        $property = self::getProperty(self::readReflection(WiderSetHookStub::class), 'virtual');

        self::assertType('string', $property->read?->type);
        self::assertType('string|int', $property->write?->type);
    }

    public function testTypeSourceIsFileOfDeclaration(): void
    {
        $type = self::getProperty(self::readReflection(UserStub::class), 'id')->read?->type;

        self::assertNotNull($type);
        self::assertSame('UserStub.php', \basename((string) $type->source->file));
        self::assertNull($type->source->path);
    }

    public function testTraitPropertyHasFileOfTrait(): void
    {
        $type = self::getProperty(self::readReflection(TraitUserStub::class), 'parent')->read?->type;

        self::assertType('?self', $type);
        self::assertSame('OwnerTrait.php', \basename((string) $type->source->file));
    }

    /**
     * @return iterable<non-empty-string, array{
     *     class-string,
     *     non-empty-string,
     *     bool,
     *     mixed,
     * }>
     */
    public static function defaultValueDataProvider(): iterable
    {
        yield 'typed without initializer' => [UserStub::class, 'id', false, null];
        yield 'nullable with null' => [UserStub::class, 'email', true, null];
        yield 'untyped without initializer' => [UserStub::class, 'untyped', true, null];
        yield 'promoted without default' => [PromotedStub::class, 'required', false, null];
        yield 'promoted with array' => [PromotedStub::class, 'users', true, []];
        yield 'promoted with int' => [PromotedStub::class, 'count', true, 1];
        yield 'promoted with null' => [PromotedStub::class, 'nullable', true, null];
        yield 'enum case' => [EnumDefaultStub::class, 'status', true, StatusStub::Active];
        yield 'promoted with array of enum cases' => [
            EnumDefaultStub::class,
            'statuses',
            true,
            [StatusStub::Active, StatusStub::Inactive],
        ];
    }

    /**
     * @param class-string $class
     * @param non-empty-string $name
     */
    #[DataProvider('defaultValueDataProvider')]
    public function testDefaultValueIsRead(string $class, string $name, bool $hasDefault, mixed $default): void
    {
        $property = self::getProperty(self::readReflection($class), $name);

        self::assertSame($hasDefault, $property->default !== null);
        self::assertSame($default, $property->default?->value);
    }

    public function testDefaultValueThatCannotBeEvaluatedIsThrown(): void
    {
        $read = static fn() => self::readReflection(BrokenDefaultStub::class);
        $exceptions = self::getExceptions($read);
        $errors = self::getErrors($read);

        self::assertCount(1, $errors);
        self::assertInstanceOf(InvalidDefaultValueException::class, $exceptions[0]);
        self::assertInstanceOf(\Error::class, $exceptions[0]->getPrevious());
        self::assertStringStartsWith(
            BrokenDefaultStub::class . '::$value: default value cannot be evaluated (',
            $errors[0],
        );
        self::assertStringEndsWith(' in BrokenDefaultStub.php', $errors[0]);
    }

    public function testExceptionOfDefaultValueIsThrownAsMetadataError(): void
    {
        $exceptions = self::getExceptions(static fn() => self::readReflection(ThrowingDefaultStub::class));

        self::assertCount(1, $exceptions);
        self::assertInstanceOf(InvalidDefaultValueException::class, $exceptions[0]);
        self::assertInstanceOf(\DomainException::class, $exceptions[0]->getPrevious());
    }

    public function testObjectDefaultValuesAreThrownAndNotSet(): void
    {
        $read = static fn() => self::readReflection(ObjectDefaultStub::class);
        $class = ObjectDefaultStub::class;
        $user = UserStub::class;

        self::assertContainsOnlyInstancesOf(InvalidDefaultValueException::class, self::getExceptions($read));
        self::assertSame([
            "{$class}::\$user: default value of type \"{$user}\" is not supported in ObjectDefaultStub.php",
            "{$class}::\$users: default value of type \"{$user}\" is not supported in ObjectDefaultStub.php",
        ], self::getErrors($read));

        try {
            $read();
        } catch (InvalidMetadataException $e) {
            self::assertNull(self::getProperty($e->metadata, 'user')->default);
            self::assertNull(self::getProperty($e->metadata, 'users')->default);
        }
    }

    #[RequiresPhp('>= 8.5.0')]
    public function testClosureDefaultValueIsThrown(): void
    {
        $class = ClosureDefaultStub::class;

        self::assertSame(
            ["{$class}::\$callback: default value of type \"Closure\" is not supported in ClosureDefaultStub.php"],
            self::getErrors(static fn() => self::readReflection(ClosureDefaultStub::class)),
        );
    }

    public function testPublicNonStaticPropertiesAreAddedInDeclarationOrder(): void
    {
        $metadata = self::readReflection(UserStub::class);

        self::assertSame(UserStub::class, $metadata->name);
        self::assertSame(['id', 'email', 'untyped', 'friend', 'displayName'], \array_keys($metadata->properties));
    }

    public function testSelectorDecidesWhichPropertiesAreAdded(): void
    {
        $metadata = self::readAsFirst(new ReflectionMetadataReader(self::createNonPublicSelector()), UserStub::class);

        self::assertSame(['secret', 'hidden'], \array_keys($metadata->properties));
    }

    public function testGivenMetadataGetsMissingPropertiesAndKeepsOtherOnes(): void
    {
        $lower = self::readAsFirst(new ReflectionMetadataReader(self::createNonPublicSelector()), UserStub::class);

        $metadata = (new ReflectionMetadataReader())->read(new \ReflectionClass(UserStub::class), $lower);

        self::assertSame(
            ['secret', 'hidden', 'id', 'email', 'untyped', 'friend', 'displayName'],
            \array_keys($metadata->properties),
        );
    }

    public function testGivenPropertyGetsNativeValuesAndKeepsOtherOnes(): void
    {
        $class = new \ReflectionClass(UserStub::class);
        $lower = new ClassMetadata(UserStub::class, ['id' => new PropertyMetadata('id', externalName: 'user_id')]);

        $id = self::getProperty((new ReflectionMetadataReader())->read($class, $lower), 'id');

        self::assertSame('user_id', $id->externalName);
        self::assertType('int', $id->read?->type);
    }

    public function testGivenTypeIsKeptForPropertyWithoutNativeType(): void
    {
        $class = new \ReflectionClass(UserStub::class);
        $type = TypeMetadata::createFromType(new NamedTypeNode(new Name('mixed')), new SourceInfo());
        $lower = new ClassMetadata(UserStub::class, ['untyped' => (new PropertyMetadata('untyped'))->withType($type)]);

        $untyped = self::getProperty((new ReflectionMetadataReader())->read($class, $lower), 'untyped');

        self::assertSame($type, $untyped->read?->type);
        self::assertSame($type, $untyped->write?->type);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function readReflection(string $class): ClassMetadata
    {
        return self::readAsFirst(new ReflectionMetadataReader(), $class);
    }

    private static function createNonPublicSelector(): PropertySelectorInterface
    {
        return new class implements PropertySelectorInterface {
            public function isSelected(\ReflectionProperty $property): bool
            {
                return !$property->isPublic();
            }
        };
    }
}
