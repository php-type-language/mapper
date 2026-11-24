<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Provider;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\DuplicateExternalNameException;
use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\InvalidTypeException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Provider\ReaderMetadataProvider;
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
use TypeLang\Mapper\Mapping\Tests\Stub\UntypedGetterStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UntypedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

final class ReaderMetadataProviderTest extends TestCase
{
    public function testReaderIsCheckedWithoutChain(): void
    {
        $provider = new ReaderMetadataProvider(new ReflectionMetadataReader());

        $metadata = $provider->read(new \ReflectionClass(UserStub::class));

        self::assertType('int', self::getOutput($metadata, 'id')->type);
        self::assertType('int', self::getInput($metadata, 'id')->type);
    }

    public function testFieldsWithoutTypeAreThrownWithTheirMembers(): void
    {
        $reflection = new ReflectionMetadataReader();

        self::assertSame(
            [UntypedStub::class . '::$untyped: type is not defined in UntypedStub.php'],
            self::getErrors(static fn() => self::provide(UntypedStub::class, $reflection)),
        );
        self::assertSame(
            [UntypedGetterStub::class . '::getValue(): type is not defined in UntypedGetterStub.php'],
            self::getErrors(static fn() => self::provide(UntypedGetterStub::class, new AttributeMetadataReader(), $reflection)),
        );
    }

    public function testSameExternalNameIsCheckedByProviderOnly(): void
    {
        $readers = [new AttributeMetadataReader(), new ReflectionMetadataReader()];

        $metadata = self::readOverReflection($readers[0], DuplicateNameStub::class);
        $errors = self::getErrors(static fn() => self::provide(DuplicateNameStub::class, ...$readers));

        self::assertSame('id', self::getProperty($metadata, 'second')->read?->name);
        self::assertSame([
            DuplicateNameStub::class . ': fields of "$first", "$second" have the same name "id" in denormalization'
                . ' in DuplicateNameStub.php',
            DuplicateNameStub::class . ': fields of "$first", "$second" have the same name "id" in normalization'
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
            NamingCollisionStub::class . ': fields of "$userId", "$user_id" have the same name "user_id"'
                . ' in denormalization in NamingCollisionStub.php',
            NamingCollisionStub::class . ': fields of "$userId", "$user_id" have the same name "user_id"'
                . ' in normalization in NamingCollisionStub.php',
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

        self::assertCount(3, $errors);
        self::assertStringStartsWith(DuplicateNameStub::class . ': unknown key "colour" in ', $errors[0]);
        self::assertStringStartsWith(
            DuplicateNameStub::class . ': fields of "$first", "$third" have the same name "first" in denormalization',
            $errors[1],
        );
    }

    public function testErrorsOfReadersAndFinalChecksAreThrownTogether(): void
    {
        $unknownKey = new ArrayMetadataReader([BrokenAttributeStub::class => ['colour' => 'red']]);
        $invalidType = new ArrayMetadataReader([BrokenAttributeStub::class => ['properties' => ['id' => 'array<']]]);
        $provider = new ReaderMetadataProvider(new ChainMetadataReader([
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
                "The mapping for class \"{$class}\" contains errors:\n  - " . \implode("\n  - ", $errors),
                $e->getMessage(),
            );
            self::assertNotNull($e->metadata->findMember('items')?->read?->type?->error);

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

            public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
            {
                $source = new SourceInfo();

                return new ClassDraft($class->name, [
                    'id' => (MemberDraft::createForProperty('id'))
                        ->withReadType(TypeMetadata::createFromError($this->read, $source))
                        ->withWriteType(TypeMetadata::createFromError($this->write, $source)),
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
