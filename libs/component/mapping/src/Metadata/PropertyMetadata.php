<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
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
    public AliasConflict $aliasConflict = AliasConflict::DEFAULT;

    /**
     * Where the value is read from on normalization and its type there,
     * {@see null} in case of the property is not normalized, such as a
     * virtual one without a get hook
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?AccessMetadata $read;

    /**
     * Where the value is written to on denormalization and its type there,
     * {@see null} in case of the property is not denormalized, such as a
     * virtual one without a set hook
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?AccessMetadata $write;

    /**
     * Whether a value must match the write type exactly, without coercion,
     * {@see null} means the mapper configuration
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?bool $strict = null;

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
     * Both directions access the backing store of the PHP property of the
     * name and have no type.
     *
     * @param non-empty-string|null $externalName name of the property in
     *        mapped data, the PHP name in case of {@see null}
     * @param list<SkipConditionInterface> $skip
     */
    public function __construct(
        /**
         * Name of the property, the key of its reader and writer
         *
         * @var non-empty-string
         */
        public readonly string $name,
        ?string $externalName = null,
        ?DefaultValueMetadata $default = null,
        array $skip = [],
    ) {
        $this->externalName = $externalName ?? $name;
        $this->isExternalNameExplicit = $externalName !== null;
        $this->read = $this->write = new AccessMetadata(new StorageAccessPoint($name));
        $this->default = $default;
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

    /**
     * Returns a copy reading the value from the given point, {@see null}
     * leaves the property out of normalization.
     */
    public function withReadAccess(?AccessMetadata $access): self
    {
        $self = clone $this;
        $self->read = $access;

        return $self;
    }

    /**
     * Returns a copy writing the value to the given point, {@see null}
     * leaves the property out of denormalization.
     */
    public function withWriteAccess(?AccessMetadata $access): self
    {
        $self = clone $this;
        $self->write = $access;

        return $self;
    }

    public function withStrict(bool $strict): self
    {
        $self = clone $this;
        $self->strict = $strict;

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

    /**
     * Returns a copy with the type of the directions of the property, a
     * missing direction is not added.
     */
    public function withType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->read = $this->read?->withType($type);
        $self->write = $this->write?->withType($type);

        return $self;
    }

    /**
     * Returns a copy with the read type, the property without the read
     * direction is returned as is.
     */
    public function withReadType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->read = $this->read?->withType($type);

        return $self;
    }

    /**
     * Returns a copy with the write type, the property without the write
     * direction is returned as is.
     */
    public function withWriteType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->write = $this->write?->withType($type);

        return $self;
    }

    public function withDefaultValue(DefaultValueMetadata $value): self
    {
        $self = clone $this;
        $self->default = $value;

        return $self;
    }
}
