<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

/**
 * State of the conversion, created once per mapper and passed to every
 * type.
 */
final class Context
{
    /**
     * The same context with the other strictness, created on the first
     * request
     */
    private ?self $sibling = null;

    public function __construct(
        /**
         * Types resolved by the class of the value at runtime, such as the
         * type of an object in `mixed`. A type known on building is given
         * to its composite type by the builder.
         */
        public readonly ClassTypes $types,
        public readonly Configuration $config = new Configuration(),
        /**
         * Runtime side of the classes: their instantiators and the readers
         * and writers of their properties
         */
        public readonly ClassAccessors $accessors = new ClassAccessors(),
        public readonly Coercers $coercers = new Coercers(),
    ) {}

    /**
     * Returns the context with the strictness overridden, such as for a
     * property with its own strictness.
     */
    public function withStrictTypes(bool $isStrict): self
    {
        if ($this->config->isStrict === $isStrict) {
            return $this;
        }

        if ($this->sibling === null) {
            $this->sibling = new self(
                types: $this->types,
                config: $this->config->withStrictTypes($isStrict),
                accessors: $this->accessors,
                coercers: $this->coercers,
            );
            $this->sibling->sibling = $this;
        }

        return $this->sibling;
    }
}
