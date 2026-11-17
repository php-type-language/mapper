<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\ClassType\Property;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of a user class by its metadata. Internal classes, such
 * as {@see \DateTimeImmutable} or {@see \stdClass}, are left to their own
 * types.
 *
 * The metadata decide which properties take part in each direction. The
 * built type is checked against the strategies of the mapper: a class whose
 * objects cannot be created, such as an abstract one or an enum, or a
 * property that cannot be accessed in its direction is an error of the type.
 *
 * @template-implements TypeBuilderInterface<object>
 */
final class ClassTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return ClassType<object>|null
     * @throws TypeDefinitionException in case of a property has no type or
     *         the class cannot be created or accessed
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?ClassType
    {
        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = \ltrim($node->name->toString(), '\\');

        if (!\class_exists($name)) {
            return null;
        }

        $class = new \ReflectionClass($name);

        if ($class->isInternal()) {
            return null;
        }

        $metadata = $context->metadata->read($class);
        $writable = $readable = [];

        foreach ($metadata->properties as $property) {
            if ($property->isWritable) {
                $writable[] = self::createProperty($class, $property, $property->write, $context);
            }

            if ($property->isReadable) {
                $readable[] = self::createProperty($class, $property, $property->read ?? $property->write, $context);
            }
        }

        $context->accessors->get($metadata);

        return new ClassType($metadata, $writable, $readable, self::getTagFields($class, $context));
    }

    /**
     * Returns the fields of the tags of the class and its bases with a
     * discriminator: the data of the class given by a base has them.
     *
     * @param \ReflectionClass<object> $class
     * @return list<string>
     */
    private static function getTagFields(\ReflectionClass $class, BuildContext $context): array
    {
        $fields = [];

        foreach ([$class->name, ...\class_parents($class->name), ...\class_implements($class->name)] as $base) {
            $reflection = new \ReflectionClass($base);

            if ($reflection->isInternal()) {
                continue;
            }

            $discriminator = $context->metadata->read($reflection)->discriminator;

            if ($discriminator !== null) {
                $fields[] = $discriminator->field;
            }
        }

        return $fields;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param TypeMetadata|null $type type of the property in the direction
     * @return Property<mixed>
     * @throws TypeDefinitionException in case of the property has no type or
     *         its source cannot read it
     */
    private static function createProperty(
        \ReflectionClass $class,
        PropertyMetadata $property,
        ?TypeMetadata $type,
        BuildContext $context,
    ): Property {
        if ($type === null) {
            throw TypeDefinitionException::becausePropertyTypeIsNotDefined($class->name, $property->name);
        }

        if ($type->type === null) {
            \assert($type->error !== null);

            throw TypeDefinitionException::becausePropertyTypeCannotBeRead($class->name, $property->name, $type->error);
        }

        return new Property($property, $context->types->getType($type->type));
    }
}
