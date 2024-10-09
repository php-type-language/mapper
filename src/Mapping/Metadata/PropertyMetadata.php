<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\Shape\NamedFieldNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

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
     * Dynamically creates AST type representation.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(LocalContext $context): ?TypeStatement
    {
        $type = $this->getType();

        return $type?->getTypeStatement($context->withDetailedTypes(false));
    }

    /**
     * Dynamically creates AST field representation.
     *
     * @codeCoverageIgnore
     */
    public function getFieldNode(LocalContext $context): ?NamedFieldNode
    {
        $statement = $this->getTypeStatement($context);

        if ($statement === null) {
            return null;
        }

        $name = $this->getName();

        if ($context->isDenormalization()) {
            $name = $this->getExportName();
        }

        return new NamedFieldNode(
            key: $name,
            of: $statement,
            optional: $this->hasDefaultValue(),
        );
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
