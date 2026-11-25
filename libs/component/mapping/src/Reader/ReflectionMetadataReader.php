<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
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
 * Base of the chain: adds the properties chosen by the selector, private
 * properties of the parents included, that have a backing store, read and
 * written there in both directions with their native types and default
 * values: the default value of the property or of the promoted constructor
 * parameter. The hooks are not called and a virtual property is not added:
 * both are declared by {@see MapGetter} and {@see MapSetter}. Over the given
 * metadata it adds the chosen properties that are missing, a given member
 * keeps its directions and accesses and takes the native type in a direction
 * accessing the backing store of the property. Other readers override the
 * values of the added properties only.
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
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $metadata ??= new ClassDraft($class->getName());
        $errors = new ErrorCollector();

        foreach (ClassProperties::getProperties($class) as $reflection) {
            $key = ClassProperties::getKey($class, $reflection);

            if ($key === ''
                || $reflection->name === ''
                || $reflection->isStatic()
                || PropertyHooks::isVirtual($reflection)
                || !$this->selector->isSelected($reflection)) {
                continue;
            }

            $member = $this->readProperty($class, $reflection, $key, $metadata->findMember($key), $errors);

            $metadata = $metadata->withMember($member);
        }

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * The types of the given member are kept for a direction without a
     * native type.
     *
     * @param \ReflectionClass<object> $class
     * @param non-empty-string $key
     */
    private function readProperty(
        \ReflectionClass $class,
        \ReflectionProperty $reflection,
        string $key,
        ?MemberDraft $member,
        ErrorCollector $errors,
    ): MemberDraft {
        /** @var non-empty-string */
        $name = $reflection->name;
        $context = $this->contexts->createFromReflection($class, $reflection);
        $type = $this->findType($reflection, PropertyAccessDirection::Read, $context);
        $access = new SlotAccessMetadata($name, ClassProperties::findPrivateClass($reflection));

        if ($member === null) {
            $new = new AccessDraft($access, $name, false, $type);
            $member = new MemberDraft($key, $new, $new);
        } elseif ($type !== null) {
            $member = $member
                ->withReadAccess(self::withNativeType($member->read, $access, $type))
                ->withWriteAccess(self::withNativeType($member->write, $access, $type));
        }

        return DefaultValueFactory::withPropertyDefault($member, $reflection, $context, $errors);
    }

    /**
     * Returns the given direction with the native type in case of it
     * accesses the backing store of the property, as is otherwise.
     */
    private static function withNativeType(
        ?AccessDraft $given,
        SlotAccessMetadata $access,
        TypeMetadata $type,
    ): ?AccessDraft {
        $slot = $given?->access;

        if (!$slot instanceof SlotAccessMetadata
            || $slot->property !== $access->property
            || $slot->class !== $access->class) {
            return $given;
        }

        return $given?->withType($type);
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
