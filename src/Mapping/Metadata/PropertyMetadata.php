<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\Shape\NamedFieldNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class PropertyMetadata extends Metadata
{
    /**
     * @var non-empty-string
     */
    private readonly string $name;

    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    private bool $readonly = false;

    /**
     * @param non-empty-string $export
     */
    public function __construct(
        private string $export,
        private ?TypeMetadata $type = null,
        ?int $createdAt = null,
    ) {
        $this->name = $this->export;

        parent::__construct($createdAt);
    }

    /**
     * Dynamically creates AST type representation.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(LocalContext $context): ?TypeStatement
    {
        $info = $this->findTypeInfo();

        return $info?->getTypeStatement();
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
     * Returns property real name.
     *
     * @api
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
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
     * Returns property public name.
     *
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
    public function setTypeInfo(TypeMetadata $type): void
    {
        $this->type = $type;
    }

    /**
     * @api
     */
    public function removeTypeInfo(): void
    {
        $this->type = null;
    }

    /**
     * @api
     */
    public function hasTypeInfo(): bool
    {
        return $this->type !== null;
    }

    /**
     * Note: The prefix "find" is used to indicate that the {@see TypeInterface}
     *       definition may be optional and method may return {@see null}.
     *       The prefix "get" is used when the value is forced to be obtained
     *       and should throw an exception if the type definition is missing.
     *
     * @api
     */
    public function findType(): ?TypeInterface
    {
        return $this->type?->getType();
    }

    /**
     * Note: The prefix "find" is used to indicate that the {@see TypeMetadata}
     *       definition may be optional and method may return {@see null}.
     *       The prefix "get" is used when the value is forced to be obtained
     *       and should throw an exception if the type definition is missing.
     *
     * @api
     */
    public function findTypeInfo(): ?TypeMetadata
    {
        return $this->type;
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
     * Note: The prefix "find" is used to indicate that the default value
     *       definition may be optional and method may return {@see null}.
     *       The prefix "get" is used when the value is forced to be obtained
     *       and should throw an exception if the default value is missing.
     *
     * @api
     */
    public function findDefaultValue(): mixed
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
