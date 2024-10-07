<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Type\TypeInterface;

final class PropertyMetadata extends Metadata
{
    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    private bool $readonly = false;

    /**
     * @param non-empty-string $export
     */
    public function __construct(
        private string $export,
        private ?TypeInterface $type = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($this->export, $createdAt);
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function setExportName(string $name): void
    {
        $this->export = $name;
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getExportName(): string
    {
        return $this->export;
    }

    /**
     * @api
     */
    public function setType(TypeInterface $type): void
    {
        $this->type = $type;
    }

    /**
     * @api
     */
    public function removeType(): void
    {
        $this->type = null;
    }

    /**
     * @api
     */
    public function getType(): ?TypeInterface
    {
        return $this->type;
    }

    /**
     * @api
     */
    public function hasType(): bool
    {
        return $this->type !== null;
    }

    /**
     * @api
     */
    public function setDefaultValue(mixed $value): void
    {
        $this->defaultValue = $value;
        $this->hasDefaultValue = true;
    }

    /**
     * @api
     */
    public function removeDefaultValue(): void
    {
        $this->defaultValue = null;
        $this->hasDefaultValue = false;
    }

    /**
     * @api
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @api
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * @api
     */
    public function markAsReadonly(bool $readonly = true): void
    {
        $this->readonly = $readonly;
    }

    /**
     * @api
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }
}
