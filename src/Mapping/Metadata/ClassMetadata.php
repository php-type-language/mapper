<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template T of object
 */
final class ClassMetadata extends Metadata
{
    /**
     * @var array<non-empty-string, PropertyMetadata>
     */
    private array $properties = [];

    /**
     * @param class-string<T> $name
     * @param iterable<array-key, PropertyMetadata> $properties
     *
     * @throws \Exception
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
     * Returns class name.
     *
     * @return class-string<T>
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @api
     */
    public function addProperty(PropertyMetadata $property): void
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function getPropertyOrCreate(string $name): PropertyMetadata
    {
        return $this->properties[$name] ??= new PropertyMetadata($name);
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function findProperty(string $name): ?PropertyMetadata
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * @return list<PropertyMetadata>
     */
    public function getProperties(): array
    {
        return \array_values($this->properties);
    }
}
