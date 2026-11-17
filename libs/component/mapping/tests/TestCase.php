<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Provider\ValidatingMetadataProvider;
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

#[Group('unit'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase
{
    /**
     * Reads the class by the chain of the given readers with the final
     * checks.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    protected static function provide(string $class, MetadataReaderInterface ...$readers): ClassMetadata
    {
        $provider = new ValidatingMetadataProvider(new ChainMetadataReader($readers));

        return $provider->read(new \ReflectionClass($class));
    }

    /**
     * Reads the class by the reader over the result of the default
     * {@see ReflectionMetadataReader}.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    protected static function readOverReflection(MetadataReaderInterface $reader, string $class): ClassMetadata
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
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    protected static function readAsFirst(MetadataReaderInterface $reader, string $class): ClassMetadata
    {
        return $reader->read(new \ReflectionClass($class), new ClassMetadata($class));
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
     * @param ClassMetadata<object> $metadata
     * @param non-empty-string $name
     */
    protected static function getProperty(ClassMetadata $metadata, string $name): PropertyMetadata
    {
        $property = $metadata->findProperty($name);

        self::assertNotNull($property, \sprintf('Property "%s" is not mapped', $name));

        return $property;
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
