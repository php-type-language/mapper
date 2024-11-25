<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * Represents an abstraction over general information about a class.
 */
final class DiscriminatorMapMetadata extends Metadata
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $field,
        /**
         * @var array<non-empty-string, TypeMetadata>
         */
        private array $map = [],
        private ?TypeMetadata $default = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * Returns class for the passed value of the defined {@see $field}.
     *
     * @api
     */
    public function findType(string $fieldValue): ?TypeMetadata
    {
        return $this->map[$fieldValue] ?? null;
    }

    /**
     * Returns {@see true} in case of the passed value of the
     * defined {@see $field} is mapped on class.
     *
     * @api
     */
    public function hasType(string $fieldValue): bool
    {
        return $this->findType($fieldValue) !== null;
    }

    /**
     * Adds type metadata for the given mapping field value.
     *
     * @api
     *
     * @param non-empty-string $fieldValue
     */
    public function addType(string $fieldValue, TypeMetadata $type): void
    {
        $this->map[$fieldValue] = $type;
    }

    /**
     * Returns class mapping.
     *
     * @api
     *
     * @return array<non-empty-string, TypeMetadata>
     */
    public function getMapping(): array
    {
        return $this->map;
    }

    /**
     * Returns discriminator field name.
     *
     * @api
     *
     * @return non-empty-string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Returns default mapping type for transformations that do not comply
     * with the specified mapping rules defined in {@see getMapping()}.
     *
     * @api
     */
    public function getDefaultType(): ?TypeMetadata
    {
        return $this->default;
    }

    /**
     * Updates default mapping type.
     *
     * @api
     */
    public function setDefaultType(?TypeMetadata $type): void
    {
        $this->default = $type;
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

        foreach ($this->getMapping() as $field => $_) {
            $participants[] = StringLiteralNode::createFromValue($field);
        }

        if (\count($participants) === 1) {
            return \reset($participants);
        }

        return new UnionTypeNode(...$participants);
    }
}
