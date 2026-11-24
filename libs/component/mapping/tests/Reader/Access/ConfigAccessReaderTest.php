<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader\Access;

use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Provider\ReaderMetadataProvider;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\AccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ChildSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ParentSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PlainAccessorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\WrongMemberStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class ConfigAccessReaderTest extends TestCase
{
    public function testConfigDeclaresDirectionsOfPropertiesAndMethods(): void
    {
        $metadata = self::readConfig(PlainAccessorStub::class, [
            'properties' => [
                'createdAt' => ['get' => ['name' => 'created_at']],
            ],
            'methods' => [
                'getName' => ['get' => ['name' => 'name', 'type' => 'non-empty-string']],
                'setName' => ['set' => ['name' => 'name', 'aliases' => ['title'], 'strict' => true]],
            ],
        ]);

        self::assertSame('created_at', self::getOutput($metadata, 'createdAt')->name);
        self::assertNotContains('createdAt', self::getNames($metadata->input->fields));

        self::assertEquals(new MethodAccessMetadata('getName'), self::getOutput($metadata, 'name')->access);
        self::assertType('non-empty-string', self::getOutput($metadata, 'name')->type);

        $setter = self::getInput($metadata, 'name');
        self::assertEquals(new MethodAccessMetadata('setName'), $setter->access);
        self::assertType('string|int', $setter->type);
        self::assertSame(['title'], $setter->aliases?->names);
        self::assertTrue($setter->strict);
    }

    public function testWrongConfigOfDirectionsIsThrown(): void
    {
        $class = PlainAccessorStub::class;
        $at = " in \$['{$class}']";

        self::assertSame(
            [
                "{$class}::\$createdAt: \"set.hook\" is true, but the property has no set hook{$at}.properties.createdAt.set",
                "{$class}::getName(): \"get\" of a method requires \"name\"{$at}.methods.getName.get",
                "{$class}::setName(): the method is not both a getter and a setter, give \"get\" or \"set\""
                    . "{$at}.methods.setName.name",
                "{$class}::\$createdAt: \"type\" is not allowed next to \"get\" or \"set\", give it in them"
                    . "{$at}.properties.createdAt.type",
                "{$class}: method \"missing\" does not exist{$at}.methods.missing",
            ],
            self::getErrors(static fn() => self::readConfig($class, [
                'properties' => [
                    'createdAt' => ['type' => 'string', 'set' => ['hook' => true]],
                ],
                'methods' => [
                    'getName' => ['get' => []],
                    'setName' => ['name' => 'name'],
                    'missing' => ['get' => ['name' => 'missing']],
                ],
            ])),
        );
    }

    public function testWrongValuesOfDirectionsAreThrownOnce(): void
    {
        $class = PlainAccessorStub::class;
        $at = " in \$['{$class}']";

        self::assertSame(
            [
                "{$class}::\$createdAt: unknown key \"strict\"{$at}.properties.createdAt.get.strict",
                "{$class}::\$createdAt: value of \"name\" must be a non-empty string{$at}.properties.createdAt.get.name",
                "{$class}::getName(): unknown key \"hook\"{$at}.methods.getName.get.hook",
                "{$class}::setName(): value of \"name\" must be a non-empty string{$at}.methods.setName.set.name",
            ],
            self::getErrors(static fn() => self::readConfig($class, [
                'properties' => [
                    'createdAt' => ['get' => ['name' => '', 'strict' => true]],
                ],
                'methods' => [
                    'getName' => ['get' => ['name' => 'name', 'hook' => true]],
                    'setName' => ['set' => ['name' => '']],
                ],
            ])),
        );
    }

    public function testConfigOfChildDeclaresPrivatePropertyOfParent(): void
    {
        $metadata = self::readConfig(ChildSecretStub::class, [
            'properties' => ['secret' => ['get' => ['name' => 'hidden']]],
        ]);

        self::assertEquals(
            new SlotAccessMetadata('secret', ParentSecretStub::class),
            self::getOutput($metadata, 'secret')->access,
        );
        self::assertSame(['own', 'hidden'], self::getNames($metadata->output->fields));
    }

    public function testWrongFormsOfMethodsAreThrownAtTheirKeys(): void
    {
        $class = PlainAccessorStub::class;
        $at = " in \$['{$class}'].methods";

        self::assertSame(
            [
                "{$class}::getName(): method must take exactly one value as a setter{$at}.getName.set",
                "{$class}::setName(): value of \"setName\" must be a map{$at}.setName",
            ],
            self::getErrors(static fn() => self::readConfig($class, [
                'methods' => [
                    'getName' => ['set' => ['name' => 'name']],
                    'setName' => 'string',
                ],
            ])),
        );
    }

    public function testStaticPropertyIsThrown(): void
    {
        $class = WrongMemberStub::class;

        self::assertSame(
            ["{$class}::\$shared: property is static and cannot be mapped in \$['{$class}'].properties.shared"],
            self::getErrors(static fn() => self::readConfig($class, [
                'properties' => ['shared' => ['get' => []]],
            ])),
        );
    }

    public function testConfigKeyOfDirectionMemberDoesNotTakePartInIsThrown(): void
    {
        $class = AccessorStub::class;

        self::assertSame(
            ["{$class}::\$createdAt: \"aliases\" is given, but the member is not denormalized"
                . " in \$['{$class}'].properties.createdAt.aliases"],
            self::getErrors(static fn() => self::provide(
                $class,
                new ArrayMetadataReader([$class => ['properties' => ['createdAt' => ['aliases' => ['c']]]]]),
                new AttributeMetadataReader(),
                new ReflectionMetadataReader(),
            )),
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<non-empty-string, mixed> $config
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function readConfig(string $class, array $config): ClassMetadata
    {
        $chain = new ChainMetadataReader([new ArrayMetadataReader([$class => $config]), new ReflectionMetadataReader()]);

        return (new ReaderMetadataProvider($chain))->read(new \ReflectionClass($class));
    }
}
