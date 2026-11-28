<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
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
use TypeLang\Mapper\Mapping\Tests\Stub\PlainSecretChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PlainSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PromotedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SensitiveStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ShadowSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ThrowingDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitUserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UntypedStub;
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
        self::assertNull(self::getProperty(self::readReflection(UntypedStub::class), 'untyped')->read?->type);
    }

    public function testPropertyAccessesStorageInBothDirections(): void
    {
        $id = self::getProperty(self::readReflection(UserStub::class), 'id');

        self::assertEquals(new SlotAccessMetadata('id'), $id->read?->access);
        self::assertEquals(new SlotAccessMetadata('id'), $id->write?->access);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testVirtualPropertyIsNotMapped(): void
    {
        $metadata = self::readReflection(VirtualHookStub::class);

        self::assertNull($metadata->findMember('computed'));
        self::assertNull($metadata->findMember('alias'));
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testPropertyWithStorageAndHookAccessesStorage(): void
    {
        $backed = self::getProperty(self::readReflection(VirtualHookStub::class), 'backed');

        self::assertEquals(new SlotAccessMetadata('backed'), $backed->read?->access);
        self::assertEquals(new SlotAccessMetadata('backed'), $backed->write?->access);
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
     *     bool,
     *     mixed,
     * }>
     */
    public static function defaultValueDataProvider(): iterable
    {
        yield 'typed without initializer' => [UserStub::class, 'id', false, false, null];
        yield 'nullable with null' => [UserStub::class, 'email', true, false, null];
        yield 'untyped without initializer' => [UntypedStub::class, 'untyped', true, false, null];
        yield 'enum case' => [EnumDefaultStub::class, 'status', true, false, null];
        yield 'promoted without default' => [PromotedStub::class, 'required', false, false, null];
        yield 'promoted with array' => [PromotedStub::class, 'users', true, true, []];
        yield 'promoted with int' => [PromotedStub::class, 'count', true, true, 1];
        yield 'promoted with null' => [PromotedStub::class, 'nullable', true, true, null];
        yield 'promoted with array of enum cases' => [
            EnumDefaultStub::class,
            'statuses',
            true,
            true,
            [StatusStub::Active, StatusStub::Inactive],
        ];
    }

    /**
     * @param class-string $class
     * @param non-empty-string $name
     */
    #[DataProvider('defaultValueDataProvider')]
    public function testDefaultOfPromotedPropertyIsWrittenOfOtherIsOptional(
        string $class,
        string $name,
        bool $isOptional,
        bool $hasDefault,
        mixed $default,
    ): void {
        $property = self::getProperty(self::readReflection($class), $name);

        self::assertSame($isOptional, $property->toInputField()?->isOptional);
        self::assertSame($hasDefault, $property->default !== null);
        self::assertSame($default, $property->default?->value);
    }

    public function testPromotedParameterWithSensitiveParameterIsSensitive(): void
    {
        $metadata = self::readReflection(SensitiveStub::class);

        self::assertTrue(self::getProperty($metadata, 'token')->isSensitive);
        self::assertFalse(self::getProperty($metadata, 'name')->isSensitive);
        self::assertFalse(self::getProperty($metadata, 'password')->isSensitive);
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
        self::assertSame(['id', 'email', 'friend', 'displayName'], \array_keys($metadata->members));
    }

    public function testSelectorDecidesWhichPropertiesAreAdded(): void
    {
        $metadata = self::readAsFirst(new ReflectionMetadataReader(self::createNonPublicSelector()), UserStub::class);

        self::assertSame(['secret', 'hidden'], \array_keys($metadata->members));
    }

    public function testSelectedPrivatePropertiesOfParentsAreMembersOfTheirOwn(): void
    {
        $reader = new ReflectionMetadataReader(self::createNonPublicSelector());
        $parent = PlainSecretStub::class . '::$secret';

        $child = self::readAsFirst($reader, PlainSecretChildStub::class);
        self::assertSame(['own', 'prot', $parent], \array_keys($child->members));
        self::assertSame('secret', self::getProperty($child, $parent)->read?->name);
        self::assertEquals(
            new SlotAccessMetadata('secret', PlainSecretStub::class),
            self::getProperty($child, $parent)->write?->access,
        );

        $shadow = self::readAsFirst($reader, ShadowSecretStub::class);
        self::assertSame(['secret', 'prot', $parent], \array_keys($shadow->members));
        self::assertEquals(
            new SlotAccessMetadata('secret', ShadowSecretStub::class),
            self::getProperty($shadow, 'secret')->read?->access,
        );
    }

    public function testGivenMetadataGetsMissingPropertiesAndKeepsOtherOnes(): void
    {
        $lower = self::readAsFirst(new ReflectionMetadataReader(self::createNonPublicSelector()), UserStub::class);

        $metadata = (new ReflectionMetadataReader())->read(new \ReflectionClass(UserStub::class), $lower);

        self::assertSame(
            ['secret', 'hidden', 'id', 'email', 'friend', 'displayName'],
            \array_keys($metadata->members),
        );
    }

    public function testGivenPropertyGetsNativeValuesAndKeepsOtherOnes(): void
    {
        $class = new \ReflectionClass(UserStub::class);
        $lower = new ClassDraft(UserStub::class, ['id' => MemberDraft::createForProperty('id')->withName('user_id')]);

        $id = self::getProperty((new ReflectionMetadataReader())->read($class, $lower), 'id');

        self::assertSame('user_id', $id->read?->name);
        self::assertType('int', $id->read?->type);
    }

    public function testGivenMemberKeepsDirectionsAndAccesses(): void
    {
        $class = new \ReflectionClass(UserStub::class);
        $hook = new AccessDraft(new HookAccessMetadata('id'), 'user_id');
        $lower = new ClassDraft(UserStub::class, ['id' => new MemberDraft('id', $hook)]);

        $id = self::getProperty((new ReflectionMetadataReader())->read($class, $lower), 'id');

        self::assertSame($hook, $id->read);
        self::assertNull($id->write);
    }

    public function testGivenTypeIsKeptForPropertyWithoutNativeType(): void
    {
        $class = new \ReflectionClass(UntypedStub::class);
        $type = TypeMetadata::createFromType(new NamedTypeNode(new Name('mixed')), new SourceInfo());
        $member = MemberDraft::createForProperty('untyped')->withType($type);
        $lower = new ClassDraft(UntypedStub::class, ['untyped' => $member]);

        $untyped = self::getProperty((new ReflectionMetadataReader())->read($class, $lower), 'untyped');

        self::assertSame($type, $untyped->read?->type);
        self::assertSame($type, $untyped->write?->type);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return ClassDraft<T>
     * @throws \Throwable
     */
    private static function readReflection(string $class): ClassDraft
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
