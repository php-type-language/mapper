<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Exception\InvalidValueException\DeclaredTypeFinder;

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
         * type of object in `mixed`. A type known on building is given
         * to its composite type by the builder.
         */
        public readonly ClassTypes $types,
        public readonly Configuration $config,
        /**
         * Runtime side of the classes: their instantiators and the readers
         * and writers of their properties
         */
        public readonly ClassAccessors $accessors,
        public readonly Coercers $coercers,
        /**
         * Finds the declared types of the nested values of an error, such
         * as of an element of a property of `list<T>`
         */
        public readonly DeclaredTypeFinder $declaredTypes = new DeclaredTypeFinder(),
        /**
         * Depth of the normalization, shared by the contexts of one mapper
         */
        public readonly Nesting $nesting = new Nesting(),
        /**
         * Language evaluating the skip expressions of the properties,
         * {@see null} evaluates an expression without its functions
         */
        public readonly ?ExpressionLanguage $expressions = null,
    ) {}

    /**
     * Returns the context with the strictness overridden, such as for a
     * property with its own strictness.
     */
    public function withStrict(bool $strict): self
    {
        if ($this->config->strict === $strict) {
            return $this;
        }

        if ($this->sibling === null) {
            $this->sibling = new self(
                types: $this->types,
                config: $this->config->withStrict($strict),
                accessors: $this->accessors,
                coercers: $this->coercers,
                declaredTypes: $this->declaredTypes,
                nesting: $this->nesting,
                expressions: $this->expressions,
            );
            $this->sibling->sibling = $this;
        }

        return $this->sibling;
    }
}
