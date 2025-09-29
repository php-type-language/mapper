<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
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

    /**
     * @var list<MatchConditionMetadata>
     */
    private array $skipWhen = [];

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
    public function getTypeStatement(Context $context): ?TypeStatement
    {
        $info = $this->findTypeInfo();

        if ($info === null) {
            return null;
        }

        $statement = clone $info->statement;

        if ($context->isDetailedTypes() || !$statement instanceof NamedTypeNode) {
            return $statement;
        }

        return new NamedTypeNode($statement->name);
    }

    /**
     * Dynamically creates AST field representation.
     *
     * @codeCoverageIgnore
     */
    public function getFieldNode(Context $context): ?NamedFieldNode
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
     * Adds new skip condition.
     *
     * @api
     */
    public function addSkipCondition(MatchConditionMetadata $expression): void
    {
        $this->skipWhen[] = $expression;
    }

    /**
     * Returns list of skip conditions.
     *
     * @api
     *
     * @return list<MatchConditionMetadata>
     */
    public function getSkipConditions(): array
    {
        return $this->skipWhen;
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
}
