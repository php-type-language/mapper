<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

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
         * @var array<non-empty-string, non-empty-string>
         */
        private array $map = [],
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * Returns class for the passed value of the defined {@see $field}.
     *
     * @return non-empty-string|null
     */
    public function findType(string $fieldValue): ?string
    {
        return $this->map[$fieldValue] ?? null;
    }

    /**
     * Returns {@see true} in case of the passed value of the
     * defined {@see $field} is mapped on class.
     */
    public function hasType(string $fieldValue): bool
    {
        return $this->findType($fieldValue) !== null;
    }

    /**
     * Returns class mapping.
     *
     * @return array<non-empty-string, non-empty-string>
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
}
