<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Mapping\Metadata\Metadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * Represents an abstraction over general information about a class.
 */
final class DiscriminatorMetadata extends Metadata
{
    public function __construct(
        /**
         * Gets discriminator field name.
         *
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * The mapping between field's value and types.
         *
         * @var non-empty-array<non-empty-string, TypeMetadata>
         */
        public readonly array $map,
        /**
         * Gets default mapping type for transformations that do not comply
         * with the specified mapping rules defined in {@see getMapping()}.
         */
        public ?TypeMetadata $default = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * Dynamically creates AST discriminator representation.
     *
     * Required to print type information in exceptions.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(): TypeStatement
    {
        $participants = [];

        foreach ($this->map as $field => $_) {
            $participants[] = StringLiteralNode::createFromValue($field);
        }

        if (\count($participants) === 1) {
            return \reset($participants);
        }

        return new UnionTypeNode(...$participants);
    }
}
