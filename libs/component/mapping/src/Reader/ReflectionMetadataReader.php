<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Reader\Reflection\PublicPropertySelector;
use TypeLang\Mapper\Mapping\Reader\Source\DefaultValueFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Reader\Exception\ReaderExceptionInterface;
use TypeLang\Reader\PropertyAccessDirection;
use TypeLang\Reader\PropertyReaderInterface;
use TypeLang\Reader\ReflectionReader;

/**
 * Base of the chain: adds the properties chosen by the selector with their
 * access points, native types and default values, the default value of the
 * property or of the promoted constructor parameter. A property with a
 * backing store is accessed there, a virtual one through its hooks and takes
 * part only in the directions it has a hook for. Over the given metadata it
 * overrides these values of the chosen properties, adds the chosen
 * properties that are missing and keeps the other ones. Other readers
 * override the values of the added properties only.
 */
final class ReflectionMetadataReader implements MetadataReaderInterface
{
    private readonly SourceContextFactory $contexts;

    public function __construct(
        private readonly PropertySelectorInterface $selector = new PublicPropertySelector(),
        /**
         * Reader of the native types of the properties
         */
        private readonly PropertyReaderInterface $nativeTypeReader = new ReflectionReader(),
    ) {
        $this->contexts = new SourceContextFactory();
    }

    /**
     * @throws InvalidMetadataException in case of a default value cannot be
     *         evaluated or is an object other than an enum case
     */
    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $metadata ??= new ClassMetadata($class->getName());
        $errors = new ErrorCollector();

        foreach ($class->getProperties() as $reflection) {
            $name = $reflection->name;

            if ($name === '' || !$this->selector->isSelected($reflection)) {
                continue;
            }

            $property = $metadata->findProperty($name) ?? new PropertyMetadata($name);
            $property = $this->readProperty($class, $reflection, $property, $errors);

            $metadata = $metadata->withProperty($property);
        }

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function readProperty(
        \ReflectionClass $class,
        \ReflectionProperty $reflection,
        PropertyMetadata $property,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $context = $this->contexts->createFromReflection($class, $reflection);
        $property = $this->readAccess($reflection, $property, $context);

        try {
            $default = DefaultValueFactory::findForProperty($reflection, $context);
        } catch (InvalidDefaultValueException $e) {
            $errors->addError($e);

            return $property;
        }

        if ($default !== null) {
            $property = $property->withDefaultValue($default);
        }

        return $property;
    }

    /**
     * A property with a backing store is accessed there in both directions
     * and has its own type in both, the signature of its `set` hook is not
     * read: the hooks are bypassed. A virtual property is accessed through
     * the hooks it has, the write type is the parameter of the `set` hook.
     * A direction without a native type keeps the given type.
     */
    private function readAccess(
        \ReflectionProperty $reflection,
        PropertyMetadata $property,
        SourceContext $context,
    ): PropertyMetadata {
        /** @var non-empty-string */
        $name = $reflection->name;
        $type = $this->findType($reflection, PropertyAccessDirection::Read, $context);

        if (!self::isVirtual($reflection)) {
            $point = new StorageAccessPoint($name);

            return $property
                ->withReadAccess(new AccessMetadata($point, $type ?? $property->read?->type))
                ->withWriteAccess(new AccessMetadata($point, $type ?? $property->write?->type));
        }

        $read = $write = null;

        if (PropertyHooks::findGet($reflection) !== null) {
            $read = new AccessMetadata(new HookAccessPoint($name), $type ?? $property->read?->type);
        }

        if (PropertyHooks::findSet($reflection) !== null) {
            $type = $this->findType($reflection, PropertyAccessDirection::Write, $context);
            $write = new AccessMetadata(new HookAccessPoint($name), $type ?? $property->write?->type);
        }

        return $property->withReadAccess($read)
            ->withWriteAccess($write);
    }

    /**
     * Returns {@see true} in case of the property has no backing store
     * (PHP 8.4+).
     */
    private static function isVirtual(\ReflectionProperty $reflection): bool
    {
        if (\PHP_VERSION_ID < 80400) {
            return false;
        }

        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        return $reflection->isVirtual();
    }

    private function findType(
        \ReflectionProperty $reflection,
        PropertyAccessDirection $direction,
        SourceContext $context,
    ): ?TypeMetadata {
        try {
            $type = $this->nativeTypeReader->findPropertyType($reflection, $direction);
        } catch (ReaderExceptionInterface $e) {
            return $context->createInvalidType($e);
        }

        if ($type === null) {
            return null;
        }

        return TypeMetadata::createFromType($type, $context->source);
    }
}
