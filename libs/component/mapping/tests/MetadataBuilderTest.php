<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCache;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCachePool;
use TypeLang\Mapper\Mapping\Tests\Stub\AttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ClassNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DocBlockStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DuplicateNameStub;
use TypeLang\Mapper\Mapping\Tests\Stub\EnumNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\InstanceNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\NamingStub;
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
                    'accounts' => ['write_type' => 'array'],
                ],
            ],
        ]);

        $metadata = MetadataBuilder::createDefault()
            ->withAddedReader($config, MetadataBuilder::ATTRIBUTE_PRIORITY + 1)
            ->build()
            ->read(new \ReflectionClass(AttributeStub::class));
        $path = '$[\'' . AttributeStub::class . '\'].properties';

        $id = self::getProperty($metadata, 'id');
        self::assertSame(5, $id->default?->value);
        self::assertSame($path . '.id.default', $id->default->source->path);

        $value = self::getProperty($metadata, 'value');
        self::assertType('string', $value->read);
        self::assertType('string', $value->write);
        self::assertSame($path . '.value', $value->read->source->path);

        $accounts = self::getProperty($metadata, 'accounts');
        self::assertType('list<' . UserStub::class . '>', $accounts->read);
        self::assertType('array', $accounts->write);
        self::assertNull($accounts->read->source->path);
        self::assertSame($path . '.accounts.write_type', $accounts->write->source->path);

        $count = self::getProperty($metadata, 'count');
        self::assertType('positive-int', $count->read);
        self::assertType('positive-int', $count->write);
        self::assertSame(1, $count->default?->value);
        self::assertNull($count->default->source->path);
    }

    public function testNewBuilderReadsReflectionOnly(): void
    {
        $metadata = (new MetadataBuilder())
            ->build()
            ->read(new \ReflectionClass(DocBlockStub::class));

        self::assertType('array', self::getProperty($metadata, 'accounts')->read);
    }

    public function testHigherPriorityOverridesLowerOne(): void
    {
        $metadata = (new MetadataBuilder())
            ->withAddedReader(self::createTypeReader('positive-int'), 10)
            ->withAddedReader(self::createTypeReader('int'))
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertType('positive-int', self::getProperty($metadata, 'id')->read);
    }

    public function testLaterReaderOfEqualPriorityOverridesEarlierOne(): void
    {
        $metadata = (new MetadataBuilder())
            ->withAddedReader(self::createTypeReader('int'))
            ->withAddedReader(self::createTypeReader('positive-int'))
            ->build()
            ->read(new \ReflectionClass(UserStub::class));

        self::assertType('positive-int', self::getProperty($metadata, 'id')->read);
    }

    public function testBuilderIsNotChangedByItsMethods(): void
    {
        $builder = new MetadataBuilder();
        $builder->withAddedReader(self::createTypeReader('positive-int'));

        $metadata = $builder->build()->read(new \ReflectionClass(UserStub::class));

        self::assertType('int', self::getProperty($metadata, 'id')->read);
    }

    public function testSpecialTagsOverrideStandardOnes(): void
    {
        $metadata = MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(TagPriorityStub::class));

        self::assertType('list<int>', self::getProperty($metadata, 'special')->read);
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

        self::assertType('list<string>', self::getProperty($metadata, 'custom')->read);
        self::assertType('array', self::getProperty($metadata, 'special')->read);
    }

    public function testStrategyNamesPropertiesWithoutExternalName(): void
    {
        $builder = MetadataBuilder::createDefault()
            ->withNamingStrategy(new SnakeCaseNamingStrategy());

        $metadata = $builder->build()->read(new \ReflectionClass(AttributeStub::class));

        self::assertSame('user_id', self::getProperty($metadata, 'id')->externalName);

        $metadata = $builder->build()->read(new \ReflectionClass(UserStub::class));

        self::assertSame('display_name', self::getProperty($metadata, 'displayName')->externalName);
    }

    public function testStrategyOfClassOverridesStrategyOfMapper(): void
    {
        $metadata = MetadataBuilder::createDefault()
            ->withNamingStrategy(new KebabCaseNamingStrategy())
            ->build()
            ->read(new \ReflectionClass(NamingStub::class));

        self::assertSame('first_name', self::getProperty($metadata, 'firstName')->externalName);
        self::assertSame('ID', self::getProperty($metadata, 'userId')->externalName);
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

        self::assertSame($expected, self::getProperty($metadata, 'firstName')->externalName);
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

        self::assertSame(['id'], \array_keys($metadata->properties));
    }

    public function testStackChecksFinalMetadata(): void
    {
        $errors = self::getErrors(static fn() => MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(DuplicateNameStub::class)));

        self::assertCount(1, $errors);
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

        self::assertType('int', self::getProperty($metadata, 'id')->read);
        self::assertCount(1, $cache->values);
    }

    #[RunInSeparateProcess]
    public function testDefaultStackWithoutPhpDocPackageSkipsDocBlocks(): void
    {
        self::hidePhpDocPackage();

        $metadata = MetadataBuilder::createDefault()
            ->build()
            ->read(new \ReflectionClass(DocBlockStub::class));

        self::assertType('array', self::getProperty($metadata, 'accounts')->read);
    }

    #[RunInSeparateProcess]
    public function testDocBlockReaderWithoutPhpDocPackageIsRejected(): void
    {
        self::hidePhpDocPackage();

        $this->expectException(MissingDependencyException::class);

        (new MetadataBuilder())->withPhpDocReader();
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
