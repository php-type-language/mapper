<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Draft;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\EnumMetadata;
use TypeLang\Mapper\Mapping\Metadata\Struct\InputStructMetadata;
use TypeLang\Mapper\Mapping\Metadata\Struct\OutputStructMetadata;
use TypeLang\Mapper\Naming\NamingStrategyInterface;

/**
 * Metadata of a class while the readers read it: its mapped members by
 * key, its polymorphism and its options. The fields are read directly and
 * changed by the `with*()` methods, each returning a changed copy.
 *
 * @template T of object
 */
final class ClassDraft
{
    /**
     * Mapped properties and methods by the key of the member, in order of
     * the keys of the data
     *
     * @var array<non-empty-string, MemberDraft>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $members;

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
     * Naming strategy of the fields the class declares without an explicit
     * name, {@see null} in case of the strategy of the mapper
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public ?NamingStrategyInterface $namingStrategy = null;

    /**
     * Naming strategies of the fields the parents declare, by the parent
     * declaring them. A parent without one names its fields by the strategy
     * of the mapper.
     *
     * @var array<class-string, NamingStrategyInterface>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $parentNamingStrategies = [];

    /**
     * Values of the cases of an enum, names of the cases of an enum without
     * values, by the other values of the data denormalized to them, empty
     * for a class
     *
     * @var array<array-key, int|string>
     *
     * @readonly
     *
     * @phpstan-readonly-allow-private-mutation
     */
    public array $aliases = [];

    /**
     * @param array<non-empty-string, MemberDraft> $members
     */
    public function __construct(
        /**
         * @var class-string<T>
         */
        public readonly string $name,
        array $members = [],
        ?DiscriminatorMetadata $discriminator = null,
    ) {
        $this->members = $members;
        $this->discriminator = $discriminator;
    }

    /**
     * Returns the metadata the draft describes: each member gives a field
     * of each direction it takes part in.
     *
     * @return ClassMetadata<T>
     */
    public function toMetadata(): ClassMetadata
    {
        $input = $output = [];

        foreach ($this->members as $member) {
            $field = $member->toInputField();

            if ($field !== null) {
                $input[] = $field;
            }

            $field = $member->toOutputField();

            if ($field !== null) {
                $output[] = $field;
            }
        }

        return new ClassMetadata(
            name: $this->name,
            input: new InputStructMetadata($input, $this->typeErrorMessage),
            output: new OutputStructMetadata($output, $this->normalizeAsArray),
            discriminator: $this->discriminator,
        );
    }

    /**
     * Returns the metadata of the enum the draft describes.
     *
     * @return EnumMetadata<T&\UnitEnum>
     */
    public function toEnumMetadata(): EnumMetadata
    {
        /** @var class-string<T&\UnitEnum> */
        $name = $this->name;

        return new EnumMetadata($name, $this->aliases);
    }

    /**
     * @param non-empty-string $key
     */
    public function findMember(string $key): ?MemberDraft
    {
        return $this->members[$key] ?? null;
    }

    /**
     * Returns a copy with the member replacing the member of the same key,
     * a new member is added to the end.
     *
     * @return self<T>
     */
    public function withMember(MemberDraft $member): self
    {
        $self = clone $this;
        $self->members[$member->key] = $member;

        return $self;
    }

    /**
     * Returns a copy with the members of the given keys first, in their
     * order, the other members after them, in their own order.
     *
     * @param list<non-empty-string> $keys
     * @return self<T>
     */
    public function withMemberOrder(array $keys): self
    {
        $self = clone $this;
        $self->members = [];

        foreach ($keys as $key) {
            if (isset($this->members[$key])) {
                $self->members[$key] = $this->members[$key];
            }
        }

        $self->members += $this->members;

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
     * @param class-string $parent
     * @return self<T>
     */
    public function withParentNamingStrategy(string $parent, NamingStrategyInterface $strategy): self
    {
        $self = clone $this;
        $self->parentNamingStrategies[$parent] = $strategy;

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
