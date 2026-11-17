<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\SkipCondition\SkipConditionInterface;

/**
 * Mapping of one property of a class. The fields are read directly and
 * changed by the `with*()` methods, each returning a changed copy.
 */
final class PropertyMetadata
{
    /**
     * Name of the property in mapped data, in both directions
     *
     * @var non-empty-string
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public string $externalName;

    /**
     * Whether {@see $externalName} is given by a reader, such as by an
     * attribute, {@see false} in case of it is the PHP name
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public bool $isExternalNameExplicit;

    /**
     * Other names of the field accepted on denormalization, in the order of
     * priority
     *
     * @var list<non-empty-string>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $aliases = [];

    /**
     * What the property takes when the data has several of its names
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public AliasConflict $aliasConflict = AliasConflict::Fail;

    /**
     * Type of the value read from the property on normalization
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?TypeMetadata $read;

    /**
     * Type of the value written to the property on denormalization
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?TypeMetadata $write;

    /**
     * Value used when mapped data has none, {@see null} in case of the
     * value is required
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?DefaultValueMetadata $default;

    /**
     * Whether the property takes part in normalization, {@see false} for a
     * property that has no value to read, such as a virtual one without a
     * get hook
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public bool $isReadable;

    /**
     * Whether the property takes part in denormalization, {@see false} for
     * a property that takes no value, such as a virtual one without a set
     * hook
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public bool $isWritable;

    /**
     * Conditions the property is left out of the normalized data under, any
     * of them is enough
     *
     * @var list<SkipConditionInterface>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $skip;

    /**
     * Template of the message of an invalid value of the property,
     * {@see null} in case of the message of the mapper
     *
     * @var non-empty-string|null
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?string $typeErrorMessage = null;

    /**
     * Template of the message of the missing field of the property,
     * {@see null} in case of the message of the mapper
     *
     * @var non-empty-string|null
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?string $undefinedErrorMessage = null;

    /**
     * @param non-empty-string|null $externalName name of the property in
     *        mapped data, the PHP name in case of {@see null}
     * @param list<SkipConditionInterface> $skip
     */
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $name,
        ?string $externalName = null,
        ?TypeMetadata $read = null,
        ?TypeMetadata $write = null,
        ?DefaultValueMetadata $default = null,
        bool $isReadable = true,
        bool $isWritable = true,
        array $skip = [],
    ) {
        $this->externalName = $externalName ?? $name;
        $this->isExternalNameExplicit = $externalName !== null;
        $this->read = $read;
        $this->write = $write;
        $this->default = $default;
        $this->isReadable = $isReadable;
        $this->isWritable = $isWritable;
        $this->skip = $skip;
    }

    /**
     * Returns a copy with the conditions replacing the given ones.
     *
     * @param list<SkipConditionInterface> $conditions
     */
    public function withSkipConditions(array $conditions): self
    {
        $self = clone $this;
        $self->skip = $conditions;

        return $self;
    }

    /**
     * @param non-empty-string $message
     */
    public function withTypeErrorMessage(string $message): self
    {
        $self = clone $this;
        $self->typeErrorMessage = $message;

        return $self;
    }

    /**
     * @param non-empty-string $message
     */
    public function withUndefinedErrorMessage(string $message): self
    {
        $self = clone $this;
        $self->undefinedErrorMessage = $message;

        return $self;
    }

    public function withReadable(bool $isReadable): self
    {
        $self = clone $this;
        $self->isReadable = $isReadable;

        return $self;
    }

    public function withWritable(bool $isWritable): self
    {
        $self = clone $this;
        $self->isWritable = $isWritable;

        return $self;
    }

    /**
     * @param non-empty-string $name
     */
    public function withExternalName(string $name): self
    {
        $self = clone $this;
        $self->externalName = $name;
        $self->isExternalNameExplicit = true;

        return $self;
    }

    /**
     * Returns a copy with the aliases replacing the given ones.
     *
     * @param list<non-empty-string> $aliases
     */
    public function withAliases(array $aliases): self
    {
        $self = clone $this;
        $self->aliases = $aliases;

        return $self;
    }

    public function withAliasConflict(AliasConflict $conflict): self
    {
        $self = clone $this;
        $self->aliasConflict = $conflict;

        return $self;
    }

    public function withType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->read = $type;
        $self->write = $type;

        return $self;
    }

    public function withReadType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->read = $type;

        return $self;
    }

    public function withWriteType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->write = $type;

        return $self;
    }

    public function withDefaultValue(DefaultValueMetadata $value): self
    {
        $self = clone $this;
        $self->default = $value;

        return $self;
    }
}
