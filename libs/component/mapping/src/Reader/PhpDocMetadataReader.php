<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\PropertyContext;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\PhpDoc\DocBlock\Tag\ParamTag\ParamTag;
use TypeLang\PhpDoc\DocBlock\Tag\ReturnTag\ReturnTag;
use TypeLang\PhpDoc\DocBlock\Tag\VarTag\VarTag;
use TypeLang\PhpDoc\DocBlockParser;

/**
 * Overrides types of the given properties with types from docblocks and
 * adds no properties. The docblock is chosen by the access point of the
 * direction: the property for the backing store, the hook and then the
 * property for a hook, a method is not read.
 *
 * Note: Requires the `type-lang/phpdoc` package.
 */
final class PhpDocMetadataReader implements OptionalMetadataReaderInterface
{
    private readonly DocBlockParser $parser;

    private readonly SourceContextFactory $contexts;

    public function __construct(
        ?DocBlockParser $parser = null,
    ) {
        if (!self::isAvailable()) {
            throw MissingDependencyException::becausePackageIsNotInstalled('type-lang/phpdoc', 'PHPDoc tags');
        }

        $this->parser = $parser ?? self::createDefaultDocBlockParser();
        $this->contexts = new SourceContextFactory();
    }

    public static function isAvailable(): bool
    {
        return \class_exists(DocBlockParser::class);
    }

    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $metadata ??= new ClassMetadata($class->getName());

        foreach ($metadata->properties as $property) {
            $read = $this->findReadType($class, $property->read);

            if ($read !== null) {
                $property = $property->withReadType($read);
            }

            // both directions of one backing store have one type
            $write = $read;

            if (!self::isSameStorage($property->read, $property->write)) {
                $write = $this->findWriteType($class, $property->write);
            }

            if ($write !== null) {
                $property = $property->withWriteType($write);
            }

            $metadata = $metadata->withProperty($property);
        }

        return $metadata;
    }

    /**
     * Reads the type of the backing store from the property, of the `get`
     * hook from its `@return`, then from the property.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findReadType(\ReflectionClass $class, ?AccessMetadata $access): ?TypeMetadata
    {
        $context = $this->findContext($class, $access);

        if ($context === null) {
            return null;
        }

        if ($access?->point instanceof StorageAccessPoint) {
            return self::findPropertyType($context);
        }

        return self::findGetHookType($context) ?? self::findPropertyType($context);
    }

    /**
     * Reads the type of the backing store from the property, of the `set`
     * hook from its `@param`, then from the property in case of the hook
     * does not declare its input.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findWriteType(\ReflectionClass $class, ?AccessMetadata $access): ?TypeMetadata
    {
        $context = $this->findContext($class, $access);

        if ($context === null) {
            return null;
        }

        $set = PropertyHooks::findSet($context->reflection);

        if ($access?->point instanceof StorageAccessPoint || $set === null) {
            return self::findPropertyType($context);
        }

        $type = self::findSetHookType($context, $set);

        if ($type !== null || self::hasOwnWriteType($context->reflection, $set)) {
            return $type;
        }

        return self::findPropertyType($context);
    }

    private static function isSameStorage(?AccessMetadata $read, ?AccessMetadata $write): bool
    {
        return $read?->point instanceof StorageAccessPoint
            && $write?->point instanceof StorageAccessPoint
            && $read->point->property === $write->point->property;
    }

    /**
     * Returns the context of the PHP property of the point, {@see null} for
     * a point without a PHP property, such as a method.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findContext(\ReflectionClass $class, ?AccessMetadata $access): ?PropertyContext
    {
        $point = $access?->point;

        if (!$point instanceof StorageAccessPoint && !$point instanceof HookAccessPoint) {
            return null;
        }

        if (!$class->hasProperty($point->property)) {
            return null;
        }

        return PropertyContext::create(
            class: $class,
            reflection: $class->getProperty($point->property),
            parser: $this->parser,
            contexts: $this->contexts,
        );
    }

    /**
     * Reads `@var` of the property, then `@param` of the constructor for a
     * promoted property.
     */
    private static function findPropertyType(PropertyContext $context): ?TypeMetadata
    {
        return self::findVarType($context) ?? self::findPromotedParamType($context);
    }

    private static function findVarType(PropertyContext $context): ?TypeMetadata
    {
        return $context->createType($context->findTag(
            docComment: $context->reflection->getDocComment(),
            class: VarTag::class,
            variable: $context->reflection->name,
        ));
    }

    private static function findPromotedParamType(PropertyContext $context): ?TypeMetadata
    {
        $constructor = $context->scope->getConstructor();

        if ($constructor === null || !$context->reflection->isPromoted()) {
            return null;
        }

        return $context->createType($context->findTag(
            docComment: $constructor->getDocComment(),
            class: ParamTag::class,
            variable: $context->reflection->name,
        ));
    }

    private static function findGetHookType(PropertyContext $context): ?TypeMetadata
    {
        $get = PropertyHooks::findGet($context->reflection);

        if ($get === null) {
            return null;
        }

        return $context->createType($context->findTag(
            docComment: $get->getDocComment(),
            class: ReturnTag::class,
            variable: $context->reflection->name,
        ));
    }

    /**
     * Returns {@see true} in case of the `set` hook takes another native
     * type than the property. Its `@param` is read before.
     */
    private static function hasOwnWriteType(\ReflectionProperty $property, \ReflectionMethod $set): bool
    {
        $parameter = $set->getParameters()[0] ?? null;

        return (string) $parameter?->getType() !== (string) $property->getType();
    }

    private static function findSetHookType(PropertyContext $context, \ReflectionMethod $set): ?TypeMetadata
    {
        $parameter = $set->getParameters()[0]->name ?? '';

        if ($parameter === '') {
            return null;
        }

        return $context->createType($context->findTag(
            docComment: $set->getDocComment(),
            class: ParamTag::class,
            variable: $parameter,
        ));
    }

    private static function createDefaultDocBlockParser(): DocBlockParser
    {
        return new DocBlockParser([
            MapperPlatform::createWithDefaultTagNames(),
        ]);
    }
}
