<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Runtime\Context;
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
         * @var non-empty-array<non-empty-string, TypeMetadata>
         */
        private readonly array $map = [],
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * Returns class for the passed value of the defined {@see $field}.
     *
     * @api
     *
     * @return non-empty-string|null
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
     * Dynamically creates AST discriminator representation.
     *
     * Required to print type information in exceptions.
     *
     * @api
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
