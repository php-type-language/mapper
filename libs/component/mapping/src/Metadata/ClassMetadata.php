<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Naming\NamingStrategyInterface;

/**
 * Mapping of a class: its mapped properties, its polymorphism and its
 * options. The fields are read directly and changed by the `with*()`
 * methods, each returning a changed copy.
 *
 * @template T of object
 */
final class ClassMetadata
{
    /**
     * Version of the format of the metadata classes of this namespace, it is
     * raised on each change of their properties, a stored metadata of
     * another version is not read
     */
    public const FORMAT_VERSION = 1;

    /**
     * Mapped properties by PHP name, in order of the output keys
     *
     * @var array<non-empty-string, PropertyMetadata>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $properties;

    /**
     * Types the data of the class is mapped to by its tag, {@see null} in
     * case of the class is mapped by its own properties
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?DiscriminatorMetadata $discriminator = null;

    /**
     * Template of the message of a value that is not of the class as a
     * whole, {@see null} in case of the message of the mapper
     *
     * @var non-empty-string|null
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?string $typeErrorMessage = null;

    /**
     * Whether an object is normalized to an array or to a {@see \stdClass},
     * {@see null} in case of the configuration of the mapper
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?bool $normalizeAsArray = null;

    /**
     * Naming strategy of the properties without an explicit name,
     * {@see null} in case of the strategy of the mapper
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?NamingStrategyInterface $namingStrategy = null;

    /**
     * Values of the cases of a backed enum by the other values of the data
     * denormalized to them, empty for a class
     *
     * @var array<array-key, int|string>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $aliases = [];

    /**
     * @param array<non-empty-string, PropertyMetadata> $properties
     */
    public function __construct(
        /**
         * @var class-string<T>
         */
        public readonly string $name,
        array $properties = [],
        ?DiscriminatorMetadata $discriminator = null,
    ) {
        $this->properties = $properties;
        $this->discriminator = $discriminator;
    }

    /**
     * @param non-empty-string $name
     */
    public function findProperty(string $name): ?PropertyMetadata
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Returns a copy with the property replacing the property of the same
     * name, a new property is added to the end.
     *
     * @return self<T>
     */
    public function withProperty(PropertyMetadata $property): self
    {
        $self = clone $this;
        $self->properties[$property->name] = $property;

        return $self;
    }

    /**
     * @return self<T>
     */
    public function withDiscriminator(DiscriminatorMetadata $discriminator): self
    {
        $self = clone $this;
        $self->discriminator = $discriminator;

        return $self;
    }

    /**
     * @param non-empty-string $message
     * @return self<T>
     */
    public function withTypeErrorMessage(string $message): self
    {
        $self = clone $this;
        $self->typeErrorMessage = $message;

        return $self;
    }

    /**
     * @return self<T>
     */
    public function withNormalizeAsArray(bool $normalizeAsArray): self
    {
        $self = clone $this;
        $self->normalizeAsArray = $normalizeAsArray;

        return $self;
    }

    /**
     * @return self<T>
     */
    public function withNamingStrategy(NamingStrategyInterface $strategy): self
    {
        $self = clone $this;
        $self->namingStrategy = $strategy;

        return $self;
    }

    /**
     * Returns a copy with the alias of the case value, replacing the same
     * alias of a lower reader.
     *
     * @return self<T>
     */
    public function withAlias(int|string $alias, int|string $value): self
    {
        $self = clone $this;
        $self->aliases[$alias] = $value;

        return $self;
    }
}
