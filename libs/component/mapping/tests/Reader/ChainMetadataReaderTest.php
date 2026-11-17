<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\AttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DuplicateNameStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnparsableTypeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class ChainMetadataReaderTest extends TestCase
{
    public function testChainWithoutReadersReturnsNoProperties(): void
    {
        $metadata = (new ChainMetadataReader([]))->read(new \ReflectionClass(UserStub::class));

        self::assertSame(UserStub::class, $metadata->name);
        self::assertSame([], $metadata->properties);
    }

    public function testLowestReaderGetsGivenMetadata(): void
    {
        $chain = new ChainMetadataReader([new AttributeMetadataReader()]);

        $metadata = self::readOverReflection($chain, AttributeStub::class);

        self::assertSame('user_id', self::getProperty($metadata, 'id')->externalName);
        self::assertType('int', self::getProperty($metadata, 'id')->read);
    }

    public function testChainDoesNotCheckFinalMetadata(): void
    {
        $chain = new ChainMetadataReader([new AttributeMetadataReader(), new ReflectionMetadataReader()]);

        $unparsable = $chain->read(new \ReflectionClass(UnparsableTypeStub::class));
        $duplicate = $chain->read(new \ReflectionClass(DuplicateNameStub::class));

        self::assertNotNull(self::getProperty($unparsable, 'items')->write?->error);
        self::assertSame('id', self::getProperty($duplicate, 'first')->externalName);
        self::assertSame('id', self::getProperty($duplicate, 'second')->externalName);
    }

    public function testNestedChainDoesNotCheckOverriddenFailure(): void
    {
        $config = new ArrayMetadataReader([UnparsableTypeStub::class => ['properties' => ['items' => 'list<int>']]]);

        $metadata = self::provide(
            UnparsableTypeStub::class,
            $config,
            new ChainMetadataReader([new AttributeMetadataReader(), new ReflectionMetadataReader()]),
        );

        self::assertType('list<int>', self::getProperty($metadata, 'items')->write);
    }

    public function testErrorsOfAllReadersAreThrownTogether(): void
    {
        $unknownKey = new ArrayMetadataReader([BrokenAttributeStub::class => ['colour' => 'red']]);
        $chain = new ChainMetadataReader([
            $unknownKey,
            new AttributeMetadataReader(),
            new ReflectionMetadataReader(),
        ]);

        try {
            $chain->read(new \ReflectionClass(BrokenAttributeStub::class));
        } catch (InvalidMetadataException $e) {
            self::assertCount(3, $e->errors);
            self::assertInstanceOf(InvalidAttributeException::class, $e->errors[0]);
            self::assertStringContainsString(': attribute "' . MapName::class . '"', $e->errors[0]->getMessage());
            self::assertInstanceOf(InvalidOptionValueException::class, $e->errors[1]);
            self::assertStringContainsString(': value of "name" ', $e->errors[1]->getMessage());
            self::assertInstanceOf(UnknownKeyException::class, $e->errors[2]);
            self::assertStringContainsString(': unknown key "colour"', $e->errors[2]->getMessage());
            self::assertNotNull($e->metadata->findProperty('items')?->read?->error);

            return;
        }

        self::fail('Metadata is expected to be invalid');
    }

    public function testErrorsOfNestedChainAreThrownTogetherWithOuterOnes(): void
    {
        $unknownKey = new ArrayMetadataReader([BrokenAttributeStub::class => ['colour' => 'red']]);

        $errors = self::getErrors(static fn() => (new ChainMetadataReader([
            $unknownKey,
            new ChainMetadataReader([new AttributeMetadataReader(), new ReflectionMetadataReader()]),
        ]))->read(new \ReflectionClass(BrokenAttributeStub::class)));

        self::assertCount(3, $errors);
        self::assertStringContainsString(': attribute "' . MapName::class . '"', $errors[0]);
        self::assertStringStartsWith(BrokenAttributeStub::class . ': unknown key "colour" in ', $errors[2]);
    }

    public function testHigherTypeOverridesStrictnessOfLowerType(): void
    {
        $config = new ArrayMetadataReader([AttributeStub::class => ['properties' => ['value' => 'string']]]);

        $metadata = self::provideDefault(AttributeStub::class, $config);

        $value = self::getProperty($metadata, 'value');
        self::assertType('string', $value->read);
        self::assertNull($value->read->isStrict);
        self::assertNull($value->write?->isStrict);
    }

    public function testStrictnessWithoutTypeIsAppliedToLowerTypes(): void
    {
        $config = new ArrayMetadataReader([
            AttributeStub::class => [
                'properties' => [
                    'id' => ['strict' => true],
                    'accounts' => ['strict' => true],
                    'value' => ['strict' => false],
                ],
            ],
        ]);

        $metadata = self::provideDefault(AttributeStub::class, $config);

        $id = self::getProperty($metadata, 'id');
        self::assertType('int', $id->read);
        self::assertTrue($id->read->isStrict);
        self::assertTrue($id->write?->isStrict);

        $accounts = self::getProperty($metadata, 'accounts');
        self::assertType('list<' . UserStub::class . '>', $accounts->read);
        self::assertTrue($accounts->read->isStrict);
        self::assertTrue($accounts->write?->isStrict);

        $value = self::getProperty($metadata, 'value');
        self::assertType('int', $value->read);
        self::assertFalse($value->read->isStrict);
        self::assertFalse($value->write?->isStrict);
    }

    public function testUnparsableDocBlockTypeOverriddenByAttributeIsNotError(): void
    {
        $metadata = self::provide(
            AttributeStub::class,
            new AttributeMetadataReader(),
            self::createPhpDocReader(['broken-var' => 'var']),
            new ReflectionMetadataReader(),
        );

        self::assertType('positive-int', self::getProperty($metadata, 'count')->read);
    }

    public function testUnparsableAttributeTypeOverriddenByConfigIsNotError(): void
    {
        $config = new ArrayMetadataReader([UnparsableTypeStub::class => ['properties' => ['items' => 'list<int>']]]);

        $metadata = self::provide(
            UnparsableTypeStub::class,
            $config,
            new AttributeMetadataReader(),
            new ReflectionMetadataReader(),
        );

        self::assertType('list<int>', self::getProperty($metadata, 'items')->write);
    }

    public function testConfigOverridesAttribute(): void
    {
        $config = new ArrayMetadataReader([
            AttributeStub::class => [
                'properties' => [
                    'id' => ['name' => 'identifier'],
                    'accounts' => 'array',
                ],
            ],
        ]);

        $metadata = self::provide(
            AttributeStub::class,
            $config,
            new AttributeMetadataReader(),
            new ReflectionMetadataReader(),
        );

        self::assertSame('identifier', self::getProperty($metadata, 'id')->externalName);
        self::assertType('array', self::getProperty($metadata, 'accounts')->read);
        self::assertType('array', self::getProperty($metadata, 'accounts')->write);
    }

    public function testBrokenAttributeIsErrorEvenIfConfigSetsSameValue(): void
    {
        $config = new ArrayMetadataReader([
            BrokenAttributeStub::class => [
                'properties' => [
                    'id' => ['name' => 'user_id'],
                    'items' => 'array',
                    'name' => ['name' => 'user_name'],
                ],
            ],
        ]);

        $errors = self::getErrors(static fn() => self::provide(
            BrokenAttributeStub::class,
            $config,
            new AttributeMetadataReader(),
            new ReflectionMetadataReader(),
        ));

        self::assertCount(2, $errors);
        self::assertStringStartsWith(
            \sprintf('%s::$id: attribute "%s" cannot be created (', BrokenAttributeStub::class, MapName::class),
            $errors[0],
        );
        self::assertStringStartsWith(
            \sprintf('%s::$name: value of "name" must be a non-empty string', BrokenAttributeStub::class),
            $errors[1],
        );
    }

    /**
     * Reads the class by the readers of the default stack with the config.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function provideDefault(string $class, ArrayMetadataReader $config): ClassMetadata
    {
        return self::provide(
            $class,
            $config,
            new AttributeMetadataReader(),
            new PhpDocMetadataReader(),
            new ReflectionMetadataReader(),
        );
    }
}
