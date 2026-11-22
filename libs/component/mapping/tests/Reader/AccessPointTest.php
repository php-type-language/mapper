<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapOnly;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\MethodAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\AccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ConflictAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\CountingGetterStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PlainAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\ChosenHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\NamedHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\OnlyMissingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\WrongSignatureStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class AccessPointTest extends TestCase
{
    public function testMethodsOfAttributesAreAccessPointsWithTheirNativeTypes(): void
    {
        $name = self::getProperty(self::readDefault(AccessorStub::class), 'name');

        self::assertEquals(new MethodAccessPoint('getName'), $name->read?->point);
        self::assertEquals(new MethodAccessPoint('setName'), $name->write?->point);
        self::assertType('string', $name->read->type);
    }

    public function testTypeAttributeOfSetterSetsWriteTypeAndStrictness(): void
    {
        $name = self::getProperty(self::readDefault(AccessorStub::class), 'name');

        self::assertType('non-empty-string', $name->write?->type);
        self::assertTrue($name->strict);
    }

    public function testTypeAttributeOfGetterIsResolvedByFileOfMethod(): void
    {
        $owner = self::getProperty(self::readDefault(AccessorStub::class), 'owner');

        self::assertType('?' . UserStub::class, $owner->read?->type);
        self::assertInstanceOf(StorageAccessPoint::class, $owner->write?->point);
    }

    public function testPhpDocOfMethodsIsReadAtTheirPoints(): void
    {
        $items = self::getProperty(self::readDefault(AccessorStub::class), 'items');

        self::assertType('list<positive-int>', $items->read?->type);
        self::assertType('list<positive-int>', $items->write?->type);
    }

    public function testOnlyAttributeLeavesPropertyOutOfOtherDirection(): void
    {
        $metadata = self::readDefault(AccessorStub::class);

        self::assertNull(self::getProperty($metadata, 'createdAt')->write);
        self::assertNotNull(self::getProperty($metadata, 'createdAt')->read);
        self::assertNull(self::getProperty($metadata, 'password')->read);
        self::assertNotNull(self::getProperty($metadata, 'password')->write);
    }

    public function testChainBuiltByHandReadsAccessPointsAfterBase(): void
    {
        $metadata = self::provide(AccessorStub::class, new AttributeMetadataReader(), new ReflectionMetadataReader());

        self::assertEquals(new MethodAccessPoint('setName'), self::getProperty($metadata, 'name')->write?->point);
        self::assertNull(self::getProperty($metadata, 'password')->read);
    }

    public function testNestedChainIsReadAsPartOfChain(): void
    {
        $metadata = (new ChainMetadataReader([
            new AttributeMetadataReader(),
            new ChainMetadataReader([self::createPhpDocReader(), new ReflectionMetadataReader()]),
        ]))->read(new \ReflectionClass(AccessorStub::class));

        // PHPDoc of the inner chain reads the types at the points of the outer attributes
        self::assertType('list<positive-int>', self::getProperty($metadata, 'items')->write?->type);
    }

    public function testWrongAccessAttributesAreThrown(): void
    {
        $class = BrokenAccessorStub::class;
        $getter = MapGetter::class;
        $setter = MapSetter::class;
        $type = MapType::class;
        $at = ' in BrokenAccessorStub.php';

        self::assertSame(
            [
                "{$class}::getName(): attribute \"{$getter}\" of a method requires the name of the property{$at}",
                "{$class}::\$missing: property is not mapped{$at}",
                "{$class}::\$name: attribute \"{$setter}\" of \"{$class}::changeName()\" is the second one"
                    . " of the property{$at}",
                "{$class}::\$title: argument \"strict\" of attribute \"{$type}\" of a getter has no effect,"
                    . " it applies to denormalization{$at}",
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testAttributesOfHooksMakeHooksAccessPoints(): void
    {
        $title = self::getProperty(self::readDefault(ChosenHookStub::class), 'title');

        self::assertEquals(new HookAccessPoint('title'), $title->read?->point);
        self::assertEquals(new HookAccessPoint('title'), $title->write?->point);
        self::assertType('non-empty-string', $title->read->type);
        self::assertType('string|int', $title->write->type);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testPropertyNameOnHookAndHookWithMethodAreThrown(): void
    {
        $class = NamedHookStub::class;
        $getter = MapGetter::class;
        $at = ' in NamedHookStub.php';

        self::assertSame(
            [
                "{$class}::\$code: attribute \"{$getter}\" of a hook takes no property name{$at}",
                "{$class}::\$title: attribute \"{$getter}\" of \"{$class}::getTitle()\" is the second one"
                    . " of the property{$at}",
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
    }

    public function testVarOfPropertyIsTypeOfGetterOfNativeTypeOfPropertyOnly(): void
    {
        $metadata = self::readDefault(CountingGetterStub::class);

        self::assertType('int', self::getProperty($metadata, 'items')->read?->type);
        self::assertType('list<string>', self::getProperty($metadata, 'tags')->read?->type);
    }

    public function testHigherReaderOverridesPointOfMethodWithTypeAttribute(): void
    {
        $config = new ArrayMetadataReader([AccessorStub::class => ['properties' => [
            'name' => ['only' => 'normalization'],
        ]]]);

        $metadata = self::provide(
            AccessorStub::class,
            $config,
            new AttributeMetadataReader(),
            new ReflectionMetadataReader(),
        );

        self::assertNull(self::getProperty($metadata, 'name')->write);
    }

    public function testWrongSignaturesOfMethodsAreThrown(): void
    {
        $class = WrongSignatureStub::class;
        $at = ' in WrongSignatureStub.php';

        self::assertSame(
            [
                "{$class}::\$a: method \"{$class}::getA()\" is not public{$at}",
                "{$class}::\$b: method \"{$class}::getB()\" is static{$at}",
                "{$class}::\$c: method \"{$class}::findC()\" requires arguments, a getter takes none{$at}",
                "{$class}::\$d: method \"{$class}::resetD()\" must take exactly one value as a setter{$at}",
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
        self::assertSame(
            ["{$class}::\$a: method \"{$class}::getA()\" is not public in \$['{$class}'].properties.a.getter"],
            self::getErrors(static fn() => self::readConfig($class, ['a' => ['getter' => 'getA']])),
        );
    }

    public function testTypesOfMethodOverridingMethodOfTraitAreResolvedByClassFile(): void
    {
        $metadata = self::readDefault(TraitAccessorStub::class);

        self::assertType('?' . StatusStub::class, self::getProperty($metadata, 'owner')->read?->type);
        self::assertType('?' . UserStub::class, self::getProperty($metadata, 'parent')->read?->type);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testOnlyDirectionPropertyDoesNotTakePartInIsThrown(): void
    {
        $class = OnlyMissingStub::class;
        $only = MapOnly::class;

        self::assertSame(
            ["{$class}::\$computed: attribute \"{$only}\" is given, but the property is not denormalized"
                . ' in OnlyMissingStub.php'],
            self::getErrors(static fn() => self::readDefault($class)),
        );
        self::assertSame(
            ["{$class}::\$computed: \"only\" is given, but the property is not denormalized"
                . " in \$['{$class}'].properties.computed.only"],
            self::getErrors(static fn() => self::provide(
                $class,
                new ArrayMetadataReader([$class => ['properties' => ['computed' => ['only' => 'denormalization']]]]),
                new ReflectionMetadataReader(),
            )),
        );
    }

    public function testPointOfDirectionLeftOutIsThrown(): void
    {
        $class = ConflictAccessorStub::class;
        $setter = MapSetter::class;
        $only = MapOnly::class;

        self::assertSame(
            [
                "{$class}::\$name: attribute \"{$setter}\" of the property is given, but attribute \"{$only}\""
                    . ' leaves its direction out in ConflictAccessorStub.php',
            ],
            self::getErrors(static fn() => self::readDefault($class)),
        );
        self::assertSame(
            [
                "{$class}::\$name: \"setter\" is given, but \"only\" leaves its direction out"
                    . " in \$['{$class}'].properties.name.setter",
            ],
            self::getErrors(static fn() => self::readConfig($class, [
                'name' => ['setter' => 'setName', 'only' => 'normalization'],
            ])),
        );
    }

    public function testConfigKeysChooseAccessPoints(): void
    {
        $metadata = self::readConfig(PlainAccessorStub::class, [
            'name' => ['getter' => 'getName', 'setter' => 'setName'],
            'createdAt' => ['only' => 'normalization'],
        ]);

        $name = self::getProperty($metadata, 'name');
        self::assertEquals(new MethodAccessPoint('getName'), $name->read?->point);
        self::assertEquals(new MethodAccessPoint('setName'), $name->write?->point);
        self::assertType('string|int', $name->write->type);
        self::assertNull(self::getProperty($metadata, 'createdAt')->write);
    }

    public function testWrongConfigKeysAreThrownOnce(): void
    {
        $class = PlainAccessorStub::class;
        $at = " in \$['{$class}'].properties";

        self::assertSame(
            [
                "{$class}::\$name: method \"missing\" of \"getter\" does not exist{$at}.name.getter",
                "{$class}::\$name: value of \"setter\" must be a method name or true{$at}.name.setter",
                "{$class}::\$createdAt: value of \"only\" must be one of \"normalization\", \"denormalization\""
                    . "{$at}.createdAt.only",
                "{$class}::\$createdAt: \"getter\" is true, but the property has no get hook{$at}.createdAt.getter",
                "{$class}::\$unknown: property does not exist{$at}.unknown",
            ],
            self::getErrors(static fn() => self::readConfig($class, [
                'name' => ['getter' => 'missing', 'setter' => 42],
                'createdAt' => ['getter' => true, 'only' => 'sideways'],
                'unknown' => ['getter' => 'getName'],
            ])),
        );
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
     * @template T of object
     * @param class-string<T> $class
     * @param array<non-empty-string, mixed> $properties
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function readConfig(string $class, array $properties): ClassMetadata
    {
        $reader = new ArrayMetadataReader([$class => ['properties' => $properties]]);

        return self::provide($class, $reader, new ReflectionMetadataReader());
    }
}
