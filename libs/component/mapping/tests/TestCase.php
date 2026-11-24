<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\FieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Provider\ReaderMetadataProvider;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\AliasPlatformStub;
use TypeLang\Parser\Traverser;
use TypeLang\Parser\Traverser\StringDumperVisitor;
use TypeLang\Parser\TypeParser;
use TypeLang\PhpDoc\DocBlockParser;
use TypeLang\PhpDoc\Platform\StandardPlatform;
use TypeLang\Type\TypeNode;

abstract class TestCase extends BaseTestCase
{
    /**
     * Reads the draft of the class by the chain of the given readers, the
     * final checks of {@see ReaderMetadataProvider} apply.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return ClassDraft<T>
     * @throws \Throwable
     */
    protected static function provide(string $class, MetadataReaderInterface ...$readers): ClassDraft
    {
        $chain = new ChainMetadataReader($readers);
        $reflection = new \ReflectionClass($class);

        (new ReaderMetadataProvider($chain))->read($reflection);

        return $chain->read($reflection);
    }

    /**
     * Reads the class by the reader over the result of the default
     * {@see ReflectionMetadataReader}.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return ClassDraft<T>
     * @throws \Throwable
     */
    protected static function readOverReflection(MetadataReaderInterface $reader, string $class): ClassDraft
    {
        $reflection = new \ReflectionClass($class);

        return $reader->read($reflection, self::readAsFirst(new ReflectionMetadataReader(), $class));
    }

    /**
     * Reads the class by the reader over empty metadata of the class, as the
     * first reader of a chain.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return ClassDraft<T>
     * @throws \Throwable
     */
    protected static function readAsFirst(MetadataReaderInterface $reader, string $class): ClassDraft
    {
        return $reader->read(new \ReflectionClass($class), new ClassDraft($class));
    }

    /**
     * Creates the phpdoc reader whose parser reads the given names as the
     * standard tags: `['broken-var' => 'var']`.
     *
     * @param array<non-empty-lowercase-string, non-empty-lowercase-string> $aliases
     */
    protected static function createPhpDocReader(array $aliases = []): PhpDocMetadataReader
    {
        return new PhpDocMetadataReader(new DocBlockParser([
            new StandardPlatform(),
            new AliasPlatformStub($aliases),
        ]));
    }

    /**
     * Runs the reading expecting errors and returns them.
     *
     * @param \Closure(): mixed $read
     * @return non-empty-list<MetadataException>
     * @throws \Throwable
     */
    protected static function getExceptions(\Closure $read): array
    {
        try {
            $read();
        } catch (InvalidMetadataException $e) {
            return $e->errors;
        }

        self::fail('Metadata is expected to be invalid');
    }

    /**
     * Runs the reading expecting errors, each one is printed as its message
     * with the base name of the file.
     *
     * @param \Closure(): mixed $read
     * @return list<string>
     * @throws \Throwable
     */
    protected static function getErrors(\Closure $read): array
    {
        return \array_map(self::printError(...), self::getExceptions($read));
    }

    /**
     * @param ClassDraft<object> $metadata
     * @param non-empty-string $name
     */
    protected static function getProperty(ClassDraft $metadata, string $name): MemberDraft
    {
        $property = $metadata->findMember($name);

        self::assertNotNull($property, \sprintf('Property "%s" is not mapped', $name));

        return $property;
    }

    /**
     * Returns the field of the denormalization written to the property.
     *
     * @param ClassMetadata<object> $metadata
     * @param non-empty-string $property
     */
    protected static function getInput(ClassMetadata $metadata, string $property): InputFieldMetadata
    {
        $field = self::findField($metadata->input->fields, $property);

        self::assertInstanceOf(InputFieldMetadata::class, $field, \sprintf('Property "%s" is not written', $property));

        return $field;
    }

    /**
     * Returns the field of the normalization read from the property.
     *
     * @param ClassMetadata<object> $metadata
     * @param non-empty-string $property
     */
    protected static function getOutput(ClassMetadata $metadata, string $property): OutputFieldMetadata
    {
        $field = self::findField($metadata->output->fields, $property);

        self::assertInstanceOf(OutputFieldMetadata::class, $field, \sprintf('Property "%s" is not read', $property));

        return $field;
    }

    /**
     * @param list<FieldMetadata> $fields
     * @return list<non-empty-string>
     */
    protected static function getNames(array $fields): array
    {
        return \array_map(static fn(FieldMetadata $field): string => $field->name, $fields);
    }

    /**
     * Finds the field of the property: accessed through the property or its
     * hook, otherwise through a method, by the name of the field.
     *
     * @template TArgField of FieldMetadata
     * @param list<TArgField> $fields
     * @param non-empty-string $property
     * @return TArgField|null
     */
    private static function findField(array $fields, string $property): ?FieldMetadata
    {
        foreach ($fields as $field) {
            $access = $field->access;

            if (($access instanceof SlotAccessMetadata || $access instanceof HookAccessMetadata)
                && $access->property === $property) {
                return $field;
            }
        }

        foreach ($fields as $field) {
            if ($field->access instanceof MethodAccessMetadata && $field->name === $property) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Asserts the type is the given one with fully qualified names.
     *
     * @phpstan-assert !null $actual
     *
     * @throws \Throwable
     */
    protected static function assertType(string $expected, ?TypeMetadata $actual): void
    {
        self::assertNotNull($actual, 'Type is not set');
        self::assertNotNull($actual->type, (string) $actual->error);

        $type = (new TypeParser())->parse($expected);

        self::assertInstanceOf(TypeNode::class, $type);
        self::assertSame(self::dump($type), self::dump($actual->type));
    }

    private static function dump(TypeNode $type): string
    {
        return Traverser::through(new StringDumperVisitor(), [$type])->output;
    }

    private static function printError(MetadataException $error): string
    {
        $file = $error->source->file;

        if ($file === null) {
            return $error->getMessage();
        }

        return \str_replace($file, \basename($file), $error->getMessage());
    }
}
