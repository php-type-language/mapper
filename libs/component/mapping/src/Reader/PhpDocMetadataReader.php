<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\AccessPointInterface;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\MethodAccessPoint;
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
 * direction: the property for the backing store, the hook or the method and
 * then the property for a hook or a method.
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
            $read = $this->findReadType($class, $property->name, $property->read);

            if ($read !== null) {
                $property = $property->withReadType($read);
            }

            // both directions of one backing store have one type
            $write = $read;

            if (!self::isSameStorage($property->read, $property->write)) {
                $write = $this->findWriteType($class, $property->name, $property->write);
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
     * hook and of the getter from its `@return`, then from the property, for
     * the getter of the native type of the property only.
     *
     * @param \ReflectionClass<object> $class
     * @param non-empty-string $name
     */
    private function findReadType(\ReflectionClass $class, string $name, ?AccessMetadata $access): ?TypeMetadata
    {
        $point = $access?->point;
        $context = $this->findContext($class, self::findPointProperty($name, $point));

        if ($context === null) {
            return null;
        }

        if ($point instanceof StorageAccessPoint) {
            return self::findPropertyType($context);
        }

        if ($point instanceof MethodAccessPoint) {
            return $this->findGetterType($class, $context, $point);
        }

        return self::findGetHookType($context) ?? self::findPropertyType($context);
    }

    /**
     * Reads the type of the backing store from the property, of the `set`
     * hook and of the setter from its `@param`, then from the property in
     * case of the parameter has the native type of the property.
     *
     * @param \ReflectionClass<object> $class
     * @param non-empty-string $name
     */
    private function findWriteType(\ReflectionClass $class, string $name, ?AccessMetadata $access): ?TypeMetadata
    {
        $point = $access?->point;
        $context = $this->findContext($class, self::findPointProperty($name, $point));

        if ($context === null) {
            return null;
        }

        if ($point instanceof StorageAccessPoint) {
            return self::findPropertyType($context);
        }

        if ($point instanceof MethodAccessPoint) {
            $setter = self::findMethod($class, $point);

            if ($setter === null) {
                return null;
            }

            return self::findSetterType($context, $context->withMethod($class, $setter, $this->contexts), $setter);
        }

        $setter = PropertyHooks::findSet($context->reflection);

        if ($setter === null) {
            return self::findPropertyType($context);
        }

        return self::findSetterType($context, $context, $setter);
    }

    /**
     * Returns the PHP property whose docblock the point reads: of the
     * backing store or the hook, the property of the name for a method.
     *
     * @param non-empty-string $name
     * @return non-empty-string|null
     */
    private static function findPointProperty(string $name, ?AccessPointInterface $point): ?string
    {
        if ($point instanceof StorageAccessPoint || $point instanceof HookAccessPoint) {
            return $point->property;
        }

        if ($point instanceof MethodAccessPoint) {
            return $name;
        }

        return null;
    }

    /**
     * Reads `@return` of the getter, then the property in case of the
     * getter returns the native type of the property: a getter may return
     * another value than the property, a hook may not.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findGetterType(
        \ReflectionClass $class,
        PropertyContext $context,
        MethodAccessPoint $point,
    ): ?TypeMetadata {
        $getter = self::findMethod($class, $point);

        if ($getter === null) {
            return null;
        }

        $getterContext = $context->withMethod($class, $getter, $this->contexts);

        $type = $getterContext->createType($getterContext->findTag(
            docComment: $getter->getDocComment(),
            class: ReturnTag::class,
            variable: $context->reflection->name,
        ));

        if ($type !== null || (string) $getter->getReturnType() !== (string) $context->reflection->getType()) {
            return $type;
        }

        return self::findPropertyType($context);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private static function findMethod(\ReflectionClass $class, MethodAccessPoint $point): ?\ReflectionMethod
    {
        if (!$class->hasMethod($point->method)) {
            return null;
        }

        return $class->getMethod($point->method);
    }

    private static function isSameStorage(?AccessMetadata $read, ?AccessMetadata $write): bool
    {
        return $read?->point instanceof StorageAccessPoint
            && $write?->point instanceof StorageAccessPoint
            && $read->point->property === $write->point->property;
    }

    /**
     * Returns the context of the PHP property, {@see null} in case of the
     * class has no property of the name.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findContext(\ReflectionClass $class, ?string $property): ?PropertyContext
    {
        if ($property === null || !$class->hasProperty($property)) {
            return null;
        }

        return PropertyContext::create(
            class: $class,
            reflection: $class->getProperty($property),
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
     * Returns {@see true} in case of the `set` hook or the setter takes
     * another native type than the property. Its `@param` is read before.
     */
    private static function hasOwnWriteType(\ReflectionProperty $property, \ReflectionMethod $setter): bool
    {
        $parameter = $setter->getParameters()[0] ?? null;

        return (string) $parameter?->getType() !== (string) $property->getType();
    }

    /**
     * Reads `@param` of the `set` hook or the setter, then the property in
     * case of the setter takes the native type of the property.
     *
     * @param PropertyContext $setterContext context of the docblock of the
     *        setter
     */
    private static function findSetterType(
        PropertyContext $context,
        PropertyContext $setterContext,
        \ReflectionMethod $setter,
    ): ?TypeMetadata {
        $parameter = $setter->getParameters()[0]->name ?? '';
        $type = null;

        if ($parameter !== '') {
            $type = $setterContext->createType($setterContext->findTag(
                docComment: $setter->getDocComment(),
                class: ParamTag::class,
                variable: $parameter,
            ));
        }

        if ($type !== null || self::hasOwnWriteType($context->reflection, $setter)) {
            return $type;
        }

        return self::findPropertyType($context);
    }

    private static function createDefaultDocBlockParser(): DocBlockParser
    {
        return new DocBlockParser([
            MapperPlatform::createWithDefaultTagNames(),
        ]);
    }
}
