<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Draft;

use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;
use TypeLang\Mapper\Mapping\Metadata\AliasesMetadata;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\SkipConditionInterface;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * Mapped member of a class while the readers read it: a property or a
 * method, with the field of each direction it takes part in. The values of
 * one direction, such as the aliases of the denormalization, are kept for
 * the member and go to the field of their direction. The fields are read
 * directly and changed by the `with*()` methods, each returning a changed
 * copy.
 */
final class MemberDraft
{
    /**
     * Where the value of the field of the normalization is read from,
     * {@see null} in case of the member is not read
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?AccessDraft $read;

    /**
     * Where the value of the field of the denormalization is written to,
     * {@see null} in case of the member is not written
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?AccessDraft $write;

    /**
     * Other names of the field of the denormalization, in the order of
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
     * What the field takes when the data has several of its names
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public AliasConflict $aliasConflict = AliasConflict::DEFAULT;

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
     * Value written when the data has no field, {@see null} in case of no
     * value is written
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?DefaultValueMetadata $default = null;

    /**
     * Whether the data may have no field of the denormalization, such as a
     * field with a default value or of a setter with an optional parameter
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public bool $isOptional = false;

    /**
     * Conditions the field of the normalization is left out of the data
     * under, any of them is enough
     *
     * @var list<SkipConditionInterface>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $skip = [];

    /**
     * Template of the message of an invalid value of the field,
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
     * Template of the message of the missing field, {@see null} in case of
     * the message of the mapper
     *
     * @var non-empty-string|null
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?string $undefinedErrorMessage = null;

    public function __construct(
        /**
         * Key of the member: the name of a property, the name of a method
         * with parentheses (`getName()`)
         *
         * @var non-empty-string
         */
        public readonly string $key,
        ?AccessDraft $read = null,
        ?AccessDraft $write = null,
    ) {
        $this->read = $read;
        $this->write = $write;
    }

    /**
     * Creates the property read and written through its backing store, the
     * fields have the name of the property.
     *
     * @param non-empty-string $name
     */
    public static function createForProperty(string $name, ?TypeMetadata $type = null): self
    {
        $access = new AccessDraft(new SlotAccessMetadata($name), $name, false, $type);

        return new self($name, $access, $access);
    }

    /**
     * Returns the field of the denormalization, {@see null} in case of the
     * member is not written.
     */
    public function toInputField(): ?InputFieldMetadata
    {
        if ($this->write === null) {
            return null;
        }

        $aliases = null;

        if ($this->aliases !== []) {
            $aliases = new AliasesMetadata($this->aliases, $this->aliasConflict);
        }

        return new InputFieldMetadata(
            name: $this->write->name,
            access: $this->write->access,
            type: $this->write->type,
            aliases: $aliases,
            strict: $this->strict,
            default: $this->default,
            isOptional: $this->isOptional || $this->default !== null,
            typeErrorMessage: $this->typeErrorMessage,
            undefinedErrorMessage: $this->undefinedErrorMessage,
        );
    }

    /**
     * Returns the field of the normalization, {@see null} in case of the
     * member is not read.
     */
    public function toOutputField(): ?OutputFieldMetadata
    {
        if ($this->read === null) {
            return null;
        }

        return new OutputFieldMetadata(
            name: $this->read->name,
            access: $this->read->access,
            type: $this->read->type,
            skip: $this->skip,
        );
    }

    /**
     * Returns a copy reading the value from the given access, {@see null}
     * leaves the member out of normalization.
     */
    public function withReadAccess(?AccessDraft $access): self
    {
        $self = clone $this;
        $self->read = $access;

        return $self;
    }

    /**
     * Returns a copy writing the value to the given access, {@see null}
     * leaves the member out of denormalization.
     */
    public function withWriteAccess(?AccessDraft $access): self
    {
        $self = clone $this;
        $self->write = $access;

        return $self;
    }

    /**
     * Returns a copy with the name of the fields of both directions, a
     * missing direction is not added.
     *
     * @param non-empty-string $name
     */
    public function withName(string $name, bool $isExplicit = true): self
    {
        $self = clone $this;
        $self->read = $this->read?->withName($name, $isExplicit);
        $self->write = $this->write?->withName($name, $isExplicit);

        return $self;
    }

    /**
     * Returns a copy with the type of both directions, a missing direction
     * is not added.
     */
    public function withType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->read = $this->read?->withType($type);
        $self->write = $this->write?->withType($type);

        return $self;
    }

    /**
     * Returns a copy with the read type, the member without the read
     * direction is returned as is.
     */
    public function withReadType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->read = $this->read?->withType($type);

        return $self;
    }

    /**
     * Returns a copy with the write type, the member without the write
     * direction is returned as is.
     */
    public function withWriteType(TypeMetadata $type): self
    {
        $self = clone $this;
        $self->write = $this->write?->withType($type);

        return $self;
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

    public function withStrict(bool $strict): self
    {
        $self = clone $this;
        $self->strict = $strict;

        return $self;
    }

    public function withDefaultValue(DefaultValueMetadata $value): self
    {
        $self = clone $this;
        $self->default = $value;

        return $self;
    }

    public function withOptional(bool $isOptional): self
    {
        $self = clone $this;
        $self->isOptional = $isOptional;

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
}
