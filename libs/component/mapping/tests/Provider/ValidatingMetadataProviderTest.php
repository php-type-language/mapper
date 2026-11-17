<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Provider;

use TypeLang\Mapper\Mapping\Exception\DuplicateExternalNameException;
use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidTypeException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Provider\ValidatingMetadataProvider;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DuplicateNameStub;
use TypeLang\Mapper\Mapping\Tests\Stub\NamingCollisionStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnparsableTypeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

final class ValidatingMetadataProviderTest extends TestCase
{
    public function testReaderIsCheckedWithoutChain(): void
    {
        $provider = new ValidatingMetadataProvider(new ReflectionMetadataReader());

        $metadata = $provider->read(new \ReflectionClass(UserStub::class));

        self::assertType('int', self::getProperty($metadata, 'id')->read);
    }

    public function testSameExternalNameIsCheckedByProviderOnly(): void
    {
        $readers = [new AttributeMetadataReader(), new ReflectionMetadataReader()];

        $metadata = self::readOverReflection($readers[0], DuplicateNameStub::class);
        $errors = self::getErrors(static fn() => self::provide(DuplicateNameStub::class, ...$readers));

        self::assertSame('id', self::getProperty($metadata, 'second')->externalName);
        self::assertSame([
            DuplicateNameStub::class . ': properties "$first", "$second" have the same external name "id"'
                . ' in DuplicateNameStub.php',
        ], $errors);
    }

    public function testSameExternalNameSetByStrategyIsError(): void
    {
        $read = static fn() => self::provide(
            NamingCollisionStub::class,
            new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy()),
            new ReflectionMetadataReader(),
        );

        self::assertContainsOnlyInstancesOf(DuplicateExternalNameException::class, self::getExceptions($read));
        self::assertSame([
            NamingCollisionStub::class . ': properties "$userId", "$user_id" have the same external name "user_id"'
                . ' in NamingCollisionStub.php',
        ], self::getErrors($read));
    }

    public function testSameExternalNameIsCheckedOverResultOfFailedReader(): void
    {
        $config = new ArrayMetadataReader([
            DuplicateNameStub::class => [
                'colour' => 'red',
                'properties' => ['third' => ['name' => 'first']],
            ],
        ]);

        $errors = self::getErrors(static fn() => self::provide(
            DuplicateNameStub::class,
            $config,
            new ReflectionMetadataReader(),
        ));

        self::assertCount(2, $errors);
        self::assertStringStartsWith(DuplicateNameStub::class . ': unknown key "colour" in ', $errors[0]);
        self::assertStringStartsWith(
            DuplicateNameStub::class . ': properties "$first", "$third" have the same external name "first"',
            $errors[1],
        );
    }

    public function testErrorsOfReadersAndFinalChecksAreThrownTogether(): void
    {
        $unknownKey = new ArrayMetadataReader([BrokenAttributeStub::class => ['colour' => 'red']]);
        $invalidType = new ArrayMetadataReader([BrokenAttributeStub::class => ['properties' => ['id' => 'array<']]]);
        $provider = new ValidatingMetadataProvider(new ChainMetadataReader([
            $unknownKey,
            new AttributeMetadataReader(),
            $invalidType,
            new ReflectionMetadataReader(),
        ]));

        try {
            $provider->read(new \ReflectionClass(BrokenAttributeStub::class));
        } catch (InvalidMetadataException $e) {
            $class = BrokenAttributeStub::class;
            $errors = \array_map(static fn(\Throwable $error): string => $error->getMessage(), $e->errors);

            self::assertSame([
                InvalidAttributeException::class,
                InvalidOptionValueException::class,
                UnknownKeyException::class,
                InvalidTypeException::class,
                InvalidTypeException::class,
            ], \array_map(static fn(\Throwable $error): string => $error::class, $e->errors));
            self::assertStringStartsWith("{$class}::\$id: attribute \"" . MapName::class . '"', $errors[0]);
            self::assertStringStartsWith("{$class}::\$name: value of \"name\" ", $errors[1]);
            self::assertStringStartsWith("{$class}: unknown key \"colour\" in ", $errors[2]);
            self::assertStringStartsWith("{$class}::\$id: ", $errors[3]);
            self::assertStringStartsWith("{$class}::\$items: ", $errors[4]);
            self::assertSame(
                "Cannot read metadata of \"{$class}\":\n  - " . \implode("\n  - ", $errors),
                $e->getMessage(),
            );
            self::assertNotNull($e->metadata->findProperty('items')?->read?->error);

            return;
        }

        self::fail('Metadata is expected to be invalid');
    }

    public function testSameFailureOfReadAndWriteTypesIsReportedOnce(): void
    {
        $errors = self::getErrors(static fn() => self::provide(
            UserStub::class,
            self::createFailingReader('broken', 'broken'),
        ));

        self::assertCount(1, $errors);
        self::assertStringStartsWith(UserStub::class . '::$id: broken', $errors[0]);
    }

    public function testDifferentFailuresOfReadAndWriteTypesAreReportedBoth(): void
    {
        $errors = self::getErrors(static fn() => self::provide(
            UserStub::class,
            self::createFailingReader('broken read', 'broken write'),
        ));

        self::assertCount(2, $errors);
        self::assertStringStartsWith(UserStub::class . '::$id: broken read', $errors[0]);
        self::assertStringStartsWith(UserStub::class . '::$id: broken write', $errors[1]);
    }

    /**
     * @param non-empty-string $read
     * @param non-empty-string $write
     */
    private static function createFailingReader(string $read, string $write): MetadataReaderInterface
    {
        return new class ($read, $write) implements MetadataReaderInterface {
            /**
             * @param non-empty-string $read
             * @param non-empty-string $write
             */
            public function __construct(
                private readonly string $read,
                private readonly string $write,
            ) {}

            public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
            {
                $source = new SourceInfo();

                return new ClassMetadata($class->name, [
                    'id' => new PropertyMetadata(
                        name: 'id',
                        read: TypeMetadata::createFromError($this->read, $source),
                        write: TypeMetadata::createFromError($this->write, $source),
                    ),
                ]);
            }
        };
    }

    public function testUnparsableAttributeTypeOverReflectionIsErrorAtAttribute(): void
    {
        $read = static fn() => self::provide(
            UnparsableTypeStub::class,
            new AttributeMetadataReader(),
            new ReflectionMetadataReader(),
        );
        $exceptions = self::getExceptions($read);
        $errors = self::getErrors($read);

        self::assertCount(1, $errors);
        self::assertInstanceOf(InvalidTypeException::class, $exceptions[0]);
        self::assertStringStartsWith(UnparsableTypeStub::class . '::$items: ', $errors[0]);
        self::assertStringEndsWith(' in UnparsableTypeStub.php', $errors[0]);
    }
}
