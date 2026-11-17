<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\DiscriminatorType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of user class or interface with a discriminator in its
 * metadata, an abstract one too. The base class is created only in case of
 * the map refers to it. A class of the map must be the base class or extend
 * it.
 *
 * A class without a discriminator is left to {@see ClassTypeBuilder}.
 *
 * @template-implements TypeBuilderInterface<object>
 */
final class DiscriminatorTypeBuilder implements TypeBuilderInterface
{
    public function __construct(
        /**
         * Builds the base class itself, in case of the map refers to it
         */
        private readonly ClassTypeBuilder $classes = new ClassTypeBuilder(),
    ) {}

    /**
     * @return DiscriminatorType<object>|null
     * @throws TypeDefinitionException in case of a type of the map is
     *         invalid or is a class other than the base one and its children
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?DiscriminatorType
    {
        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = \ltrim($node->name->toString(), '\\');

        if (!\class_exists($name) && !\interface_exists($name)) {
            return null;
        }

        $class = new \ReflectionClass($name);

        if ($class->isInternal()) {
            return null;
        }

        $metadata = $context->metadata->read($class);
        $discriminator = $metadata->discriminator;

        if ($discriminator === null) {
            return null;
        }

        $types = $tags = [];

        foreach ($discriminator->map as $tag => $type) {
            $types[$tag] = $this->createType($metadata, $tag, $type, $context);

            $child = self::findClass($type);

            if ($child !== null) {
                $tags[$child] ??= $tag;
            }
        }

        $default = null;

        if ($discriminator->default !== null) {
            $default = $this->createType($metadata, null, $discriminator->default, $context);
        }

        /** @var DiscriminatorType<object> */
        return new DiscriminatorType($metadata, $types, $tags, $default);
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @param int|string|null $tag tag of the map, {@see null} for the type of
     *        the data with no known tag
     * @return TypeInterface<object>
     * @throws TypeDefinitionException in case of the type is invalid or is a
     *         class other than the base one and its children
     */
    private function createType(
        ClassMetadata $metadata,
        int|string|null $tag,
        TypeMetadata $type,
        BuildContext $context,
    ): TypeInterface {
        $node = $type->type;

        if ($node === null) {
            \assert($type->error !== null);

            throw TypeDefinitionException::becauseDiscriminatorTypeCannotBeRead($metadata->name, $tag, $type->error);
        }

        $child = self::findClass($type);

        if ($child === $metadata->name) {
            return $this->classes->tryBuild($node, $context)
                ?? throw TypeDefinitionException::becauseDiscriminatorBaseCannotBeBuilt($metadata->name, $tag);
        }

        if ($child !== null && !\is_a($child, $metadata->name, true)) {
            throw TypeDefinitionException::becauseDiscriminatorClassIsNotChild($metadata->name, $tag, $child);
        }

        /** @var TypeInterface<object> */
        return $context->types->getType($node);
    }

    /**
     * Returns the class named by the type, {@see null} in case of the type
     * is not a name of a class or an interface.
     *
     * @return class-string|null
     */
    private static function findClass(TypeMetadata $type): ?string
    {
        $node = $type->type;

        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = \ltrim($node->name->toString(), '\\');

        if (!\class_exists($name) && !\interface_exists($name)) {
            return null;
        }

        return (new \ReflectionClass($name))->name;
    }
}
