<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Represents an abstraction over general information about a class.
 *
 * @template T of object
 */
final class ClassMetadata extends Metadata
{
    /**
     * Contains a list of class fields available for
     * normalization and denormalization.
     *
     * @var array<non-empty-string, PropertyMetadata>
     */
    private array $properties = [];

    /**
     * Gets information about the normalization method of an object.
     *
     * - Contains {@see true} if the object should be normalized as
     *   an associative {@see array}.
     *
     * - Contains {@see false} if the object should be normalized as an
     *   anonymous {@see object}.
     *
     * - Contains {@see null} if the system settings for this option
     *   should be used.
     */
    public ?bool $isNormalizeAsArray = null;

    /**
     * Gets {@see DiscriminatorMapMetadata} information about a class
     * discriminator map, or returns {@see null} if no such metadata has been
     * registered in the {@see ClassMetadata} instance.
     */
    public ?DiscriminatorMapMetadata $discriminator;

    /**
     * @param iterable<array-key, PropertyMetadata> $properties
     */
    public function __construct(
        /**
         * Gets full qualified class name.
         *
         * @var class-string<T>
         */
        public readonly string $name,
        iterable $properties = [],
        ?DiscriminatorMapMetadata $discriminator = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        $this->discriminator = $discriminator;
    }

    /**
     * Dynamically creates AST class representation.
     *
     * Required to print type information in exceptions.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(Context $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->name);
        }

        $fields = [];

        foreach ($this->getProperties() as $property) {
            $field = $property->getFieldNode($context);

            if ($field === null) {
                continue;
            }

            $fields[] = $field;
        }

        if ($fields === []) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode($this->name, fields: new FieldsListNode($fields));
    }

    /**
     * Adds {@see PropertyMetadata} property information to
     * the {@see ClassMetadata} instance.
     *
     * @api
     */
    public function addProperty(PropertyMetadata $property): void
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * Returns {@see PropertyMetadata} information about the property.
     *
     * If it was previously absent, it creates a new one.
     *
     * @api
     *
     * @param non-empty-string $name
     */
    public function getPropertyOrCreate(string $name): PropertyMetadata
    {
        return $this->properties[$name] ??= new PropertyMetadata($name);
    }

    /**
     * Returns {@see PropertyMetadata} information about a property,
     * or returns {@see null} if no such property has been registered
     * in the {@see ClassMetadata} instance.
     *
     * @api
     *
     * @param non-empty-string $name
     */
    public function findProperty(string $name): ?PropertyMetadata
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Returns {@see true} if the {@see PropertyMetadata} property information
     * was registered in the {@see ClassMetadata} instance
     * and {@see false} otherwise.
     *
     * @api
     *
     * @param non-empty-string $name
     */
    public function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * Returns a list of registered {@see PropertyMetadata} properties.
     *
     * @return list<PropertyMetadata>
     */
    public function getProperties(): array
    {
        return \array_values($this->properties);
    }
}
