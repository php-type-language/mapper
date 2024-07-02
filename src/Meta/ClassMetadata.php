<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Context\LocalContext;
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
     * @var list<callable():PropertyMetadata>
     */
    private array $lazyInitializedProperties = [];

    /**
     * @param class-string<T> $name
     * @param iterable<array-key, PropertyMetadata> $properties
     * @throws \Exception
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
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->getName());
        }

        $fields = [];

        $childContext = $context->merge(new Context(
            detailedTypes: false,
        ));

        foreach ($this->getProperties() as $property) {
            $type = $property->getType();

            $fields[] = new NamedFieldNode(
                key: $childContext->isDenormalization()
                    ? $property->getExportName()
                    : $property->getName(),
                of: $type === null
                    ? new NamedTypeNode('mixed')
                    : $type->getTypeStatement($childContext),
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

    private function addLazyProperty(callable $resolver): void
    {
        $this->lazyInitializedProperties[] = $resolver;
    }

    /**
     * @api
     *
     * @return self<T>
     */
    public function withAddedProperty(PropertyMetadata|callable $property): self
    {
        $self = clone $this;

        if ($property instanceof PropertyMetadata) {
            $self->addProperty($property);
        } else {
            $self->addLazyProperty($property);
        }

        return $self;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function findPropertyByName(string $name): ?PropertyMetadata
    {
        $this->initializeProperties();

        return $this->properties[$name] ?? null;
    }

    /**
     * @return list<PropertyMetadata>
     */
    public function getProperties(): array
    {
        $this->initializeProperties();

        return \array_values($this->properties);
    }

    private function initializeProperties(): void
    {
        foreach ($this->lazyInitializedProperties as $resolver) {
            $property = $resolver();

            $this->properties[$property->getName()] = $property;
        }

        $this->lazyInitializedProperties = [];
    }

    public function __serialize(): array
    {
        $this->initializeProperties();

        return [
            ...parent::__serialize(),
            'properties' => $this->getProperties(),
        ];
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->properties = $data['properties'];
    }
}
