<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader\Access;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\EmptySkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Mapping\OnUndefinedError;
use TypeLang\Mapper\Mapping\Provider\ReaderMetadataProvider;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\SkipWhenNull;
use TypeLang\Mapper\Mapping\Tests\Stub\AccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ChildSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ClashingSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\CountingGetterStub;
use TypeLang\Mapper\Mapping\Tests\Stub\HidingSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ParentSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\ChosenHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\NamedHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\WrongHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SkippedSecretChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SkippedSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\WrongArgumentStub;
use TypeLang\Mapper\Mapping\Tests\Stub\WrongMemberStub;
use TypeLang\Mapper\Mapping\Tests\Stub\WrongSignatureStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class AttributeAccessReaderTest extends TestCase
{
    public function testAttributesOfPropertyDeclareItsDirectionsAndNames(): void
    {
        $metadata = self::readDefault(AccessorStub::class);

        $createdAt = self::getOutput($metadata, 'createdAt');
        self::assertSame('created_at', $createdAt->name);
        self::assertEquals(new SlotAccessMetadata('createdAt'), $createdAt->access);
        self::assertNotContains('createdAt', self::getNames($metadata->input->fields));

        $password = self::getInput($metadata, 'password');
        self::assertSame('secret', $password->name);
        self::assertSame(['pass'], $password->aliases?->names);
        self::assertSame('Password is required', $password->undefinedErrorMessage);
        self::assertNotContains('secret', self::getNames($metadata->output->fields));

        self::assertSame('code', self::getOutput($metadata, 'code')->name);
        self::assertEquals([new NullSkipCondition()], self::getOutput($metadata, 'code')->skip);
        self::assertSame('code_in', self::getInput($metadata, 'code')->name);
    }

    public function testMethodsDeclareFieldsOfTheirOwn(): void
    {
        $metadata = self::readDefault(AccessorStub::class);

        $getter = self::getOutput($metadata, 'name');
        self::assertEquals(new MethodAccessMetadata('getName'), $getter->access);
        self::assertType('string', $getter->type);
        self::assertEquals([new EmptySkipCondition()], $getter->skip);

        $setter = self::getInput($metadata, 'name');
        self::assertEquals(new MethodAccessMetadata('setName'), $setter->access);
        self::assertType('non-empty-string', $setter->type);
        self::assertTrue($setter->strict);
        self::assertFalse($setter->isOptional);

        $items = self::getInput($metadata, 'items');
        self::assertType('list<positive-int>', self::getOutput($metadata, 'items')->type);
        self::assertType('list<positive-int>', $items->type);
        self::assertSame(['list'], $items->aliases?->names);
        self::assertTrue($items->isOptional);
        self::assertNull($items->default);

        self::assertType('?' . UserStub::class, self::getOutput($metadata, 'owner')->type);
    }

    public function testTypeOfGetterIsItsSignatureAndDocBlockOnly(): void
    {
        $metadata = self::readDefault(CountingGetterStub::class);

        self::assertType('int', self::getOutput($metadata, 'count')->type);
        self::assertType('list<string>', self::getOutput($metadata, 'items')->type);
        self::assertType('array', self::getOutput($metadata, 'raw')->type);
    }

    public function testTypesOfMethodsAreResolvedByFileOfMethod(): void
    {
        $metadata = self::readDefault(TraitAccessorStub::class);

        self::assertType('?' . StatusStub::class, self::getOutput($metadata, 'owner')->type);
        self::assertType('?' . UserStub::class, self::getOutput($metadata, 'parent')->type);
    }

    public function testWrongAttributesAreThrown(): void
    {
        $class = BrokenAccessorStub::class;
        $at = ' in BrokenAccessorStub.php';

        self::assertSame(
            [
                \sprintf('%s::getName(): attribute "%s" of a method requires the name of the field%s', $class, MapGetter::class, $at),
                \sprintf(
                    '%s::$helped: attribute "%s" is not allowed next to "%s", give its value in their arguments%s',
                    $class,
                    MapName::class,
                    MapGetter::class,
                    $at,
                ),
                \sprintf('%s::$written: attribute "%s" is given, but the member is not normalized%s', $class, SkipWhenNull::class, $at),
                \sprintf('%s::getSkipped(): attribute "%s" is allowed only next to "MapGetter"%s', $class, SkipWhenNull::class, $at),
                \sprintf('%s::getTitle(): attribute "%s" is not allowed on a method or a hook%s', $class, MapType::class, $at),
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    public function testWrongSignaturesOfMethodsAreThrown(): void
    {
        $class = WrongSignatureStub::class;
        $at = ' in WrongSignatureStub.php';

        self::assertSame(
            [
                "{$class}::getA(): method is not public{$at}",
                "{$class}::getB(): method is static{$at}",
                "{$class}::findC(): method requires arguments, a getter takes none{$at}",
                "{$class}::resetD(): method must take exactly one value as a setter{$at}",
                "{$class}::setE(): method must take exactly one value as a setter{$at}",
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testAttributesOfHooksMakeHooksAccesses(): void
    {
        $metadata = self::readDefault(ChosenHookStub::class);

        $read = self::getOutput($metadata, 'title');
        self::assertEquals(new HookAccessMetadata('title'), $read->access);
        self::assertSame('title', $read->name);
        self::assertType('non-empty-string', $read->type);

        $write = self::getInput($metadata, 'title');
        self::assertEquals(new HookAccessMetadata('title'), $write->access);
        self::assertSame('heading', $write->name);
        self::assertType('string|int', $write->type);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testWrongAttributesOfHooksAreThrown(): void
    {
        $class = WrongHookStub::class;
        $at = ' in WrongHookStub.php';

        self::assertSame(
            [
                \sprintf('%s::$wrongHook: attribute "%s" is not allowed on the get hook%s', $class, MapSetter::class, $at),
                \sprintf('%s::$twice: attribute "%s" is given both on the property and on its hook%s', $class, MapGetter::class, $at),
                \sprintf(
                    '%s::$virtual: attribute "%s" accesses the backing store, but the property is virtual:'
                        . ' give it on the hook%s',
                    $class,
                    MapGetter::class,
                    $at,
                ),
                \sprintf(
                    '%s::$helped: attribute "%s" has no effect, a virtual property is mapped by "MapGetter" or'
                        . ' "MapSetter" on its hooks%s',
                    $class,
                    MapType::class,
                    $at,
                ),
                \sprintf(
                    '%s::$skippedOnProperty: attribute "%s" of the property has no effect, the get hook reads it:'
                        . ' give it on the hook%s',
                    $class,
                    SkipWhenNull::class,
                    $at,
                ),
                \sprintf(
                    '%s::$skippedOnSetHook: attribute "%s" is given, but the member is not normalized%s',
                    $class,
                    SkipWhenNull::class,
                    $at,
                ),
                \sprintf('%s::$typedHook: attribute "%s" is not allowed on a method or a hook%s', $class, MapType::class, $at),
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testFieldsOfOneDirectionSharingNameAreThrown(): void
    {
        $class = NamedHookStub::class;

        self::assertSame(
            [\sprintf('%s: fields of "$title", "getTitle()" have the same name "title" in normalization'
                . ' in NamedHookStub.php', $class)],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    public function testPrivatePropertyOfParentIsMappedInChild(): void
    {
        $metadata = self::readDefault(ChildSecretStub::class);

        $access = new SlotAccessMetadata('secret', ParentSecretStub::class);
        $read = self::getOutput($metadata, 'secret');
        self::assertEquals($access, $read->access);
        self::assertType('non-empty-string', $read->type);
        self::assertEquals($access, self::getInput($metadata, 'secret')->access);
        self::assertSame(['own', 'secret'], self::getNames($metadata->output->fields));

        $parent = self::readDefault(ParentSecretStub::class);
        self::assertEquals($access, self::getOutput($parent, 'secret')->access);
    }

    public function testPrivatePropertiesOfParentAndChildOfOneNameAreMembersOfTheirOwn(): void
    {
        $fields = self::readDefault(HidingSecretStub::class)->output->fields;

        self::assertSame(['child_secret', 'secret'], self::getNames($fields));
        self::assertEquals(new SlotAccessMetadata('secret', HidingSecretStub::class), $fields[0]->access);
        self::assertEquals(new SlotAccessMetadata('secret', ParentSecretStub::class), $fields[1]->access);
    }

    public function testFieldsOfPropertyOfChildAndPrivatePropertyOfParentSharingNameAreThrown(): void
    {
        $class = ClashingSecretStub::class;
        $message = '%s: fields of "$secret", "%s::$secret" have the same name "secret" in %s in ClashingSecretStub.php';

        self::assertSame(
            [
                \sprintf($message, $class, ParentSecretStub::class, 'denormalization'),
                \sprintf($message, $class, ParentSecretStub::class, 'normalization'),
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    public function testArgumentsOfAccessOverrideValuesOfLowerReaderOnly(): void
    {
        $config = [AccessorStub::class => ['properties' => [
            'createdAt' => ['get' => ['name' => 'from_config']],
            'code' => ['set' => ['type' => 'non-empty-string']],
        ]]];

        $lower = self::readWithConfig($config, 10);
        self::assertSame('created_at', self::getOutput($lower, 'createdAt')->name);
        self::assertSame('code_in', self::getInput($lower, 'code')->name);

        $higher = self::readWithConfig($config, 400);
        self::assertSame('from_config', self::getOutput($higher, 'createdAt')->name);
        self::assertSame('code_in', self::getInput($higher, 'code')->name);
        self::assertType('non-empty-string', self::getInput($higher, 'code')->type);
        self::assertNotContains('code', self::getNames($higher->output->fields));
    }

    public function testWrongArgumentsAreThrownOnce(): void
    {
        $class = WrongArgumentStub::class;
        $at = ' in WrongArgumentStub.php';

        self::assertSame(
            [
                \sprintf(
                    '%s::$read: attribute "%s" is given, but the member is not denormalized%s',
                    $class,
                    MapAlias::class,
                    $at,
                ),
                \sprintf('%s::$id: value of "name" must be a non-empty string%s', $class, $at),
                \sprintf('%s::$id: value of "type" must be a non-empty string%s', $class, $at),
                \sprintf('%s::$id: value of "onTypeError" must be a non-empty string%s', $class, $at),
                \sprintf(
                    '%s::getName(): attribute "%s" is not allowed on a method or a hook%s',
                    $class,
                    MapName::class,
                    $at,
                ),
                \sprintf(
                    '%s::setName(): attribute "%s" is not allowed on a method or a hook%s',
                    $class,
                    OnUndefinedError::class,
                    $at,
                ),
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    public function testMembersThatCannotBeAccessedAreThrown(): void
    {
        $class = WrongMemberStub::class;
        $at = ' in WrongMemberStub.php';

        self::assertSame(
            [
                \sprintf('%s::$shared: property is static and cannot be mapped%s', $class, $at),
                \sprintf('%s::__construct(): method is the constructor or a magic method%s', $class, $at),
                \sprintf('%s::touch(): method returns void, a getter returns a value%s', $class, $at),
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    public function testErrorOfPrivatePropertyOfParentNamesClassDeclaringIt(): void
    {
        self::assertSame(
            [\sprintf('%s::$note: property is not mapped, give it "MapGetter" or "MapSetter" or choose it by the selector in SkippedSecretStub.php', SkippedSecretStub::class)],
            self::getErrors(static fn() => self::readDefault(SkippedSecretChildStub::class)),
        );
    }

    public function testNestedChainReadsTypesAtAccessesOfOuterChain(): void
    {
        $metadata = (new ReaderMetadataProvider(new ChainMetadataReader([
            new AttributeMetadataReader(),
            new ChainMetadataReader([self::createPhpDocReader(), new ReflectionMetadataReader()]),
        ])))->read(new \ReflectionClass(AccessorStub::class));

        self::assertType('list<positive-int>', self::getInput($metadata, 'items')->type);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function readDefault(string $class): ClassMetadata
    {
        return MetadataBuilder::createDefault()->build()->read(new \ReflectionClass($class));
    }

    /**
     * @param array<class-string, mixed> $config
     * @return ClassMetadata<AccessorStub>
     * @throws \Throwable
     */
    private static function readWithConfig(array $config, int $priority): ClassMetadata
    {
        return MetadataBuilder::createDefault()
            ->withAddedReader(new ArrayMetadataReader($config), $priority)
            ->build()
            ->read(new \ReflectionClass(AccessorStub::class));
    }
}
