<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\EnumDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ObjectDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\VirtualHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php85\ClosureDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PromotedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ThrowingDefaultStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitUserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class ReflectionMetadataReaderTest extends TestCase
{
    public function testNativeTypesAreRead(): void
    {
        $metadata = self::readReflection(UserStub::class);

        $id = self::getProperty($metadata, 'id');
        self::assertType('int', $id->read);
        self::assertType('int', $id->write);
        self::assertType('?string', self::getProperty($metadata, 'email')->read);
        self::assertNull(self::getProperty($metadata, 'untyped')->read);
    }

    public function testPropertyTakesPartInBothDirections(): void
    {
        $id = self::getProperty(self::readReflection(UserStub::class), 'id');

        self::assertTrue($id->isReadable);
        self::assertTrue($id->isWritable);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testVirtualPropertyTakesPartInDirectionsOfItsHooks(): void
    {
        $metadata = self::readReflection(VirtualHookStub::class);

        $computed = self::getProperty($metadata, 'computed');
        self::assertTrue($computed->isReadable);
        self::assertFalse($computed->isWritable);

        $alias = self::getProperty($metadata, 'alias');
        self::assertFalse($alias->isReadable);
        self::assertTrue($alias->isWritable);

        $backed = self::getProperty($metadata, 'backed');
        self::assertTrue($backed->isReadable);
        self::assertTrue($backed->isWritable);
    }

    public function testTypeSourceIsFileOfDeclaration(): void
    {
        $type = self::getProperty(self::readReflection(UserStub::class), 'id')->read;

        self::assertNotNull($type);
        self::assertSame('UserStub.php', \basename((string) $type->source->file));
        self::assertNull($type->source->path);
    }

    public function testTraitPropertyHasFileOfTrait(): void
    {
        $type = self::getProperty(self::readReflection(TraitUserStub::class), 'parent')->read;

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
        self::assertType('int', $id->read);
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
