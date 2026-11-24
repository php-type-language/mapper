<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCache;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCachePool;
use TypeLang\Mapper\Mapping\Tests\Stub\AttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ClassNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DeclarationOrderStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DocBlockStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DuplicateNameStub;
use TypeLang\Mapper\Mapping\Tests\Stub\EnumNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\InstanceNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\KebabChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\NamedChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\NamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PlainChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PlainParentStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PropertyAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\RenamedSecretChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\RenamedSecretStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TagPriorityStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Naming\KebabCaseNamingStrategy;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

final class MetadataBuilderTest extends TestCase
{
    public function testDefaultStackTakesHigherReaderForEachValue(): void
    {
        $config = new ArrayMetadataReader([
            AttributeStub::class => [
                'properties' => [
                    'id' => ['default' => 5],
                    'value' => 'string',
                    'accounts' => ['get' => [], 'set' => ['type' => 'array']],
                ],
            ],
        ]);

        $metadata = MetadataBuilder::createDefault()
            ->withAddedReader($config, MetadataBuilder::ATTRIBUTE_PRIORITY + 1)
            ->build()
            ->read(new \ReflectionClass(AttributeStub::class));
        $path = '$[\'' . AttributeStub::class . '\'].properties';

        $id = self::getInput($metadata, 'id');
        self::assertSame(5, $id->default?->value);
        self::assertSame($path . '.id.default', $id->default->source->path);

        $value = self::getOutput($metadata, 'value');
        self::assertType('string', $value->type);
        self::assertType('string', self::getInput($metadata, 'value')->type);
        self::assertSame($path . '.value', $value->type?->source->path);

        $read = self::getOutput($metadata, 'accounts');
        $write = self::getInput($metadata, 'accounts');
        self::assertType('list<' . UserStub::class . '>', $read->type);
        self::assertType('array', $write->type);
        self::assertNull($read->type?->source->path);
        self::assertSame($path . '.accounts.set.type', $write->type?->source->path);

        $count = self::getInput($metadata, 'count');
        self::assertType('positive-int', self::getOutput($metadata, 'count')->type);
        self::assertType('positive-int', $count->type);
        self::assertTrue($count->isOptional);
        self::assertNull($count->default);
    }

    public function testNewBuilderReadsReflectionOnly(): void
    {
        $metadata = (new MetadataBuilder())
            ->build()
            ->read(new \ReflectionClass(DocBlockStub::class));

        self::assertType('array', self::getOutput($metadata, 'accounts')->type);
    }

    public function testHigherPriorityOverridesLowerOne(): void
    {
        $metadata = (new MetadataBuilder())
            ->withAddedReader(self::createTypeReader('positive-int'), 10)
            ->withAddedReader(self::createTypeReader('int'))
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertType('positive-int', self::getOutput($metadata, 'id')->type);
    }

    public function testHelperOfDirectionLeftOutByHigherSourceIsNotApplied(): void
    {
        $config = new ArrayMetadataReader([PropertyAliasStub::class => ['properties' => [
            'email' => ['get' => []],
        ]]]);

        $metadata = (new MetadataBuilder())
            ->withAttributeReader()
            ->withAddedReader($config, 10)
            ->build()
            ->read(new \ReflectionClass(PropertyAliasStub::class));

        self::assertSame(['email', 'name'], self::getNames($metadata->output->fields));
        self::assertSame(['name'], self::getNames($metadata->input->fields));
    }

    public function testFieldsAreInOrderOfDeclaration(): void
    {
        $metadata = MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(DeclarationOrderStub::class));

        self::assertSame(['id', 'name', 'age', 'first'], self::getNames($metadata->output->fields));
        self::assertSame(['id', 'name', 'age', 'first'], self::getNames($metadata->input->fields));
    }

    public function testLaterReaderOfEqualPriorityOverridesEarlierOne(): void
    {
        $metadata = (new MetadataBuilder())
            ->withAddedReader(self::createTypeReader('int'))
            ->withAddedReader(self::createTypeReader('positive-int'))
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertType('positive-int', self::getOutput($metadata, 'id')->type);
    }

    public function testBuilderIsNotChangedByItsMethods(): void
    {
        $builder = new MetadataBuilder();
        $builder->withAddedReader(self::createTypeReader('positive-int'));

        $metadata = $builder->build()->read(new \ReflectionClass(UserStub::class));

        self::assertType('int', self::getOutput($metadata, 'id')->type);
    }

    public function testSpecialTagsOverrideStandardOnes(): void
    {
        $metadata = MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(TagPriorityStub::class));

        self::assertType('list<int>', self::getOutput($metadata, 'special')->type);
    }

    public function testDocBlockReaderReadsGivenTags(): void
    {
        $metadata = (new MetadataBuilder())
            ->withPhpDocReader()
            ->withPhpDocReader(new MapperPlatform(
                var: 'api-var',
                varAliases: [],
                return: 'api-return',
                returnAliases: [],
                param: 'api-param',
                paramAliases: [],
            ), 10)
            ->build()
            ->read(new \ReflectionClass(TagPriorityStub::class));

        self::assertType('list<string>', self::getOutput($metadata, 'custom')->type);
        self::assertType('array', self::getOutput($metadata, 'special')->type);
    }

    public function testStrategyNamesPropertiesWithoutExternalName(): void
    {
        $builder = MetadataBuilder::createDefault()
            ->withNamingStrategy(new SnakeCaseNamingStrategy());

        $metadata = $builder->build()->read(new \ReflectionClass(AttributeStub::class));

        self::assertSame('user_id', self::getOutput($metadata, 'id')->name);

        $metadata = $builder->build()->read(new \ReflectionClass(UserStub::class));

        self::assertSame('display_name', self::getOutput($metadata, 'displayName')->name);
    }

    public function testStrategyOfClassOverridesStrategyOfMapper(): void
    {
        $metadata = MetadataBuilder::createDefault()
            ->withNamingStrategy(new KebabCaseNamingStrategy())
            ->build()
            ->read(new \ReflectionClass(NamingStub::class));

        self::assertSame('first_name', self::getOutput($metadata, 'firstName')->name);
        self::assertSame('ID', self::getOutput($metadata, 'userId')->name);
    }

    /**
     * @return iterable<non-empty-string, array{class-string, non-empty-string}>
     */
    public static function strategyOfClassDataProvider(): iterable
    {
        yield 'name of built-in strategy' => [NamingStub::class, 'first_name'];
        yield 'instance' => [InstanceNamingStub::class, 'first-name'];
        yield 'case of enum' => [EnumNamingStub::class, 'FIRSTNAME'];
        yield 'class' => [ClassNamingStub::class, 'FirstName'];
    }

    /**
     * @param class-string $class
     * @param non-empty-string $expected
     */
    #[DataProvider('strategyOfClassDataProvider')]
    public function testStrategyOfClassNamesPropertiesWithoutStrategyOfMapper(string $class, string $expected): void
    {
        $metadata = MetadataBuilder::createDefault()->build()->read(new \ReflectionClass($class));

        self::assertSame($expected, self::getOutput($metadata, 'firstName')->name);
    }

    /**
     * @return iterable<non-empty-string, array{class-string, array<array-key, mixed>, list<non-empty-string>}>
     */
    public static function strategyOfDeclaringClassDataProvider(): iterable
    {
        yield 'attribute of parent' => [NamedChildStub::class, [], ['lastName', 'first_name']];
        yield 'attributes of parent and child' => [KebabChildStub::class, [], ['last-name', 'first_name']];
        yield 'config of parent' => [
            PlainChildStub::class,
            [PlainParentStub::class => ['naming_strategy' => 'snake_case']],
            ['lastName', 'first_name'],
        ];
    }

    /**
     * @param class-string $class
     * @param array<array-key, mixed> $config
     * @param list<non-empty-string> $expected
     */
    #[DataProvider('strategyOfDeclaringClassDataProvider')]
    public function testStrategyOfClassNamesFieldsItDeclaresOnly(string $class, array $config, array $expected): void
    {
        $metadata = MetadataBuilder::createDefault()
            ->withAddedReader(new ArrayMetadataReader($config), 100)
            ->build()
            ->read(new \ReflectionClass($class));

        self::assertSame($expected, self::getNames($metadata->output->fields));
    }

    public function testSelectorChoosesMappedProperties(): void
    {
        $selector = new class implements PropertySelectorInterface {
            public function isSelected(\ReflectionProperty $property): bool
            {
                return $property->name === 'id';
            }
        };

        $metadata = MetadataBuilder::createDefault()
            ->withReflectionReader($selector)
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertSame(['id'], \array_map(static fn(InputFieldMetadata $field): string => $field->name, $metadata->input->fields));
    }

    public function testHelperOfSelectedPrivatePropertyOfParentAppliesInChild(): void
    {
        $parent = self::readWithAllProperties(RenamedSecretStub::class);
        $child = self::readWithAllProperties(RenamedSecretChildStub::class);

        self::assertSame(['renamed'], self::getNames($parent->output->fields));
        self::assertSame(['renamed'], self::getNames($child->output->fields));
    }

    public function testStackChecksFinalMetadata(): void
    {
        $errors = self::getErrors(static fn() => MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(DuplicateNameStub::class)));

        // one name in both directions
        self::assertCount(2, $errors);
    }

    public function testStackKeepsMetadataInMemory(): void
    {
        $provider = MetadataBuilder::createDefault()->build();

        $first = $provider->read(new \ReflectionClass(UserStub::class));
        $second = $provider->read(new \ReflectionClass(UserStub::class));

        self::assertSame($first, $second);
    }

    public function testStackStoresMetadataInEachCache(): void
    {
        $pool = new ArrayCachePool();
        $cache = new ArrayCache();

        MetadataBuilder::createDefault()
            ->withPsr16Cache($cache, 'hot_')
            ->withPsr6Cache($pool, 'cold_')
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertStringStartsWith('hot_', (string) \array_key_first($cache->values));
        self::assertStringStartsWith('cold_', (string) \array_key_first($pool->values));
    }

    public function testFirstCacheIsFilledFromNextOne(): void
    {
        $pool = new ArrayCachePool();

        MetadataBuilder::createDefault()
            ->withPsr6Cache($pool, 'cold_')
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        $cache = new ArrayCache();

        $metadata = (new MetadataBuilder())
            ->withAddedReader(self::createTypeReader('positive-int'))
            ->withPsr16Cache($cache, 'hot_')
            ->withPsr6Cache($pool, 'cold_')
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertType('int', self::getOutput($metadata, 'id')->type);
        self::assertCount(1, $cache->values);
    }

    #[RunInSeparateProcess]
    public function testDefaultStackWithoutPhpDocPackageSkipsDocBlocks(): void
    {
        self::hidePhpDocPackage();

        $metadata = MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(DocBlockStub::class));

        self::assertType('array', self::getOutput($metadata, 'accounts')->type);
    }

    #[RunInSeparateProcess]
    public function testDocBlockReaderWithoutPhpDocPackageIsRejected(): void
    {
        self::hidePhpDocPackage();

        $this->expectException(MissingDependencyException::class);

        (new MetadataBuilder())->withPhpDocReader();
    }

    /**
     * @template TArgClass of object
     * @param class-string<TArgClass> $class
     * @return ClassMetadata<TArgClass>
     */
    private static function readWithAllProperties(string $class): ClassMetadata
    {
        $selector = new class implements PropertySelectorInterface {
            public function isSelected(\ReflectionProperty $property): bool
            {
                return true;
            }
        };

        return MetadataBuilder::createDefault()
            ->withReflectionReader($selector)
            ->build()
            ->read(new \ReflectionClass($class));
    }

    /**
     * @param non-empty-string $type
     */
    private static function createTypeReader(string $type): ArrayMetadataReader
    {
        return new ArrayMetadataReader([UserStub::class => ['properties' => ['id' => $type]]]);
    }

    /**
     * Makes the classes of the `type-lang/phpdoc` package unloadable in the
     * current process.
     */
    private static function hidePhpDocPackage(): void
    {
        $loaders = \spl_autoload_functions();

        foreach ($loaders as $loader) {
            \spl_autoload_unregister($loader);
        }

        \spl_autoload_register(static function (string $class) use ($loaders): void {
            if (\str_starts_with($class, 'TypeLang\\PhpDoc\\')) {
                return;
            }

            foreach ($loaders as $loader) {
                $loader($class);
            }
        });

        self::assertFalse(PhpDocMetadataReader::isAvailable());
    }
}
