<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\DiscriminatorType;
use TypeLang\Mapper\Type\Extractor\DiscriminatorTagExtractor;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of user class or interface with a discriminator in its
 * metadata or in the metadata of a parent, an abstract one too.
 *
 * A class with its own discriminator selects the type by the whole map, a
 * class of the map must be the class itself or extend it. A class with the
 * discriminator of a parent selects the type by the part of the map with
 * the class and its children, an object is normalized the same through any
 * class of the hierarchy:
 *
 * ```
 * #[DiscriminatorMap(field: 'type', map: ['square' => Square::class])]
 * abstract class Shape {}
 * abstract class Polygon extends Shape {}
 * final class Square extends Polygon {}
 *
 * $mapper->normalize(new Square(), Polygon::class); // ['type' => 'square']
 * ```
 *
 * A class of the map without children in it and a class without a
 * discriminator are left to {@see ClassTypeBuilder}.
 *
 * @template-implements TypeBuilderInterface<object>
 */
final class DiscriminatorTypeBuilder implements TypeBuilderInterface
{
    public function __construct(
        /**
         * Builds the class itself, in case of the map refers to it
         */
        private readonly ClassTypeBuilder $classes = new ClassTypeBuilder(),
    ) {}

    /**
     * @return DiscriminatorType<object>|null
     * @throws TypeDefinitionException in case of a type of the map is
     *         invalid or is a class other than the base one and its children
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?DiscriminatorType
    {
        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = $node->name->toClassString();

        if ($name === null || (!\class_exists($name) && !\interface_exists($name))) {
            return null;
        }

        $class = new \ReflectionClass($name);

        if ($class->isInternal()) {
            return null;
        }

        $metadata = $context->metadata->read($class);

        if ($metadata->discriminator !== null) {
            return $this->createOwnType($node, $metadata, $context);
        }

        $owner = self::findOwner($class, $context);

        if ($owner === null) {
            return null;
        }

        return $this->createInheritedType($node, $metadata, $owner, $context);
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @return DiscriminatorType<object>
     * @throws TypeDefinitionException in case of a type of the map is
     *         invalid or is a class other than the base one and its children
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
     */
    private function createOwnType(
        NamedTypeNode $node,
        ClassMetadata $metadata,
        BuildContext $context,
    ): DiscriminatorType {
        $discriminator = $metadata->discriminator;

        \assert($discriminator !== null);

        $types = [];

        foreach ($discriminator->map as $tag => $type) {
            $types[$tag] = $this->createType($node, $metadata, $metadata, $tag, $type, $context);
        }

        $default = null;

        if ($discriminator->default !== null) {
            $default = $this->createType($node, $metadata, $metadata, null, $discriminator->default, $context);
        }

        /** @var DiscriminatorType<object> */
        return new DiscriminatorType(
            metadata: $metadata,
            owner: $metadata,
            types: $types,
            tags: DiscriminatorTagExtractor::extractIndex($discriminator),
            default: $default,
            untagged: $default,
        );
    }

    /**
     * Creates the type by the part of the map of the parent with the class
     * and its children, {@see null} in case of the class has no children in
     * the map and is built as a class.
     *
     * @param ClassMetadata<object> $metadata
     * @param ClassMetadata<object> $owner parent with the discriminator
     * @return DiscriminatorType<object>|null
     * @throws TypeDefinitionException in case of a type of the part is
     *         invalid
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
     */
    private function createInheritedType(
        NamedTypeNode $node,
        ClassMetadata $metadata,
        ClassMetadata $owner,
        BuildContext $context,
    ): ?DiscriminatorType {
        $discriminator = $owner->discriminator;

        \assert($discriminator !== null);

        $class = $metadata->name;
        $index = DiscriminatorTagExtractor::extractIndex($discriminator);
        $nearest = DiscriminatorTagExtractor::extract($index, $class);
        $fallback = $discriminator->default;
        $fallbackClass = null;

        if ($fallback !== null) {
            $fallbackClass = DiscriminatorTagExtractor::extractClass($fallback);
        }

        $children = [];

        foreach ($index as $child => $tag) {
            if ($child !== $class && \is_a($child, $class, true)) {
                $children[] = $child;
            }
        }

        if ($children === [] && ($nearest !== null || $fallbackClass === $class)) {
            return null;
        }

        $types = $tags = [];
        $self = null;

        // tags of the class itself first, the nearest mapped parent gives
        // its tag to the class outside the map
        if ($nearest !== null) {
            $self = $this->createSelf($node, $metadata, $nearest, $context);
            $mapped = \array_search($nearest, $index, true);

            foreach ($discriminator->map as $tag => $type) {
                if (DiscriminatorTagExtractor::extractClass($type) === $mapped) {
                    $types[$tag] = $self;
                }
            }

            $tags[$class] = $nearest;
        }

        foreach ($discriminator->map as $tag => $type) {
            $child = DiscriminatorTagExtractor::extractClass($type);

            if ($child !== null && \in_array($child, $children, true)) {
                $types[$tag] = $this->createType($node, $metadata, $owner, $tag, $type, $context);
                $tags[$child] ??= $tag;
            }
        }

        // data with an unknown tag, its type creates the class or its child
        $default = null;

        if ($fallback !== null && $fallbackClass !== null && \is_a($fallbackClass, $class, true)) {
            $default = $this->createType($node, $metadata, $owner, null, $fallback, $context);
        }

        // object without a tag, normalized as by the parent
        $untagged = $default;

        if ($fallback !== null && $fallbackClass !== null && \is_a($class, $fallbackClass, true)) {
            $untagged ??= $this->createType($node, $metadata, $owner, null, $fallback, $context);
        }

        /** @var DiscriminatorType<object> */
        return new DiscriminatorType(
            metadata: $metadata,
            owner: $owner,
            types: $types,
            tags: $tags,
            default: $default,
            untagged: $untagged,
            own: $self,
        );
    }

    /**
     * Returns the nearest parent or interface of the class with a
     * discriminator, the parents first.
     *
     * @param \ReflectionClass<object> $class
     * @return ClassMetadata<object>|null
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
     */
    private static function findOwner(\ReflectionClass $class, BuildContext $context): ?ClassMetadata
    {
        foreach ([...\class_parents($class->name), ...\class_implements($class->name)] as $name) {
            $reflection = new \ReflectionClass($name);

            if ($reflection->isInternal()) {
                continue;
            }

            $metadata = $context->metadata->read($reflection);

            if ($metadata->discriminator !== null) {
                return $metadata;
            }
        }

        return null;
    }

    /**
     * Creates the type of the class itself without a discriminator.
     *
     * @param ClassMetadata<object> $metadata
     * @return TypeInterface<object>
     * @throws TypeDefinitionException in case of the class cannot be built
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
     */
    private function createSelf(
        NamedTypeNode $node,
        ClassMetadata $metadata,
        int|string|null $tag,
        BuildContext $context,
    ): TypeInterface {
        return $this->classes->tryBuild($node, $context)
            ?? throw TypeDefinitionException::becauseDiscriminatorBaseCannotBeBuilt($metadata->name, $tag);
    }

    /**
     * @param ClassMetadata<object> $metadata class of the built type
     * @param ClassMetadata<object> $owner class with the discriminator
     * @param int|string|null $tag tag of the map, {@see null} for the type of
     *        the data with no known tag
     * @return TypeInterface<object>
     * @throws TypeDefinitionException in case of the type is invalid or is a
     *         class other than the class with the discriminator and its
     *         children
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
     */
    private function createType(
        NamedTypeNode $node,
        ClassMetadata $metadata,
        ClassMetadata $owner,
        int|string|null $tag,
        TypeMetadata $type,
        BuildContext $context,
    ): TypeInterface {
        $child = $type->type;

        if ($child === null) {
            \assert($type->error !== null);

            throw TypeDefinitionException::becauseDiscriminatorTypeCannotBeRead($owner->name, $tag, $type->error);
        }

        $class = DiscriminatorTagExtractor::extractClass($type);

        if ($class === $metadata->name) {
            return $this->createSelf($node, $metadata, $tag, $context);
        }

        if ($class !== null && !\is_a($class, $owner->name, true)) {
            throw TypeDefinitionException::becauseDiscriminatorClassIsNotChild($owner->name, $tag, $class);
        }

        /** @var TypeInterface<object> */
        return $context->types->getType($child);
    }
}
