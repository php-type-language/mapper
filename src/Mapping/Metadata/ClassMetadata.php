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
     * Contains a {@see bool} flag that is responsible for converting the
     * object into an associative {@see array} during normalization.
     *
     * If {@see null}, then the system setting should be used.
     */
    private ?bool $normalizeAsArray = null;

    /**
     * @param class-string<T> $name
     * @param iterable<array-key, PropertyMetadata> $properties
     */
    public function __construct(
        private readonly string $name,
        iterable $properties = [],
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);

        foreach ($properties as $property) {
            $this->addProperty($property);
        }
    }

    /**
     * Dynamically creates AST class representation.
     *
     * Required to print type information in exceptions.
     *
     * @api
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(Context $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->getName());
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
            return new NamedTypeNode($this->getName());
        }

        return new NamedTypeNode($this->getName(), fields: new FieldsListNode($fields));
    }

    /**
     * Returns full qualified class name.
     *
     * @return class-string<T>
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns information about the normalization method of an object.
     *
     * - Returns {@see true} if the object should be normalized as
     *   an associative {@see array}.
     *
     * - Returns {@see false} if the object should be normalized as an
     *   anonymous {@see object}.
     *
     * - Returns {@see null} if the system settings for this option
     *   should be used.
     *
     * @api
     */
    public function isNormalizeAsArray(): ?bool
    {
        return $this->normalizeAsArray;
    }

    /**
     * Forces the object normalization option.
     *
     * @api
     */
    public function shouldNormalizeAsArray(?bool $enabled = null): void
    {
        $this->normalizeAsArray = $enabled;
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
