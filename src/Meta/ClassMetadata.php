<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\Shape\NamedFieldNode;
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
     */
    public function __construct(
        string $name,
        iterable $properties = [],
        ?int $createdAt = null,
    ) {
        parent::__construct($name, $createdAt);

        foreach ($properties as $property) {
            $this->addProperty($property);
        }
    }

    /**
     * @api
     */
    public function getTypeStatement(bool $export = true): TypeStatement
    {
        $fields = [];

        foreach ($this->getProperties() as $property) {
            $fields[] = new NamedFieldNode(
                key: $export ? $property->getExportName() : $property->getName(),
                of: $property->getTypeStatement() ?? new NamedTypeNode('mixed'),
                optional: $property->hasDefaultValue(),
            );
        }

        return new NamedTypeNode(
            name: $this->getName(),
            fields: new FieldsListNode($fields),
        );
    }

    /**
     * @return \ReflectionClass<T>
     * @throws \ReflectionException
     */
    public function getReflection(): \ReflectionClass
    {
        return new \ReflectionClass($this->getName());
    }

    /**
     * @return class-string<T>
     */
    public function getName(): string
    {
        /** @var class-string<T> */
        return parent::getName();
    }

    private function addProperty(PropertyMetadata $property): void
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * @api
     *
     * @return self<T>
     */
    public function withAddedProperty(PropertyMetadata $property): self
    {
        $self = clone $this;
        $self->addProperty($property);

        return $self;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function findPropertyByName(string $name): ?PropertyMetadata
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @return list<PropertyMetadata>
     */
    public function getProperties(): array
    {
        return \array_values($this->properties);
    }
}
