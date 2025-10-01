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
     * Gets property public name.
     *
     * @var non-empty-string
     */
    public string $alias;

    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    /**
     * An error message that occurs when a property
     * contains an invalid value.
     *
     * @var non-empty-string|null
     */
    public ?string $typeErrorMessage = null;

    /**
     * The error message that occurs when the specified
     * property does not have a value.
     *
     * @var non-empty-string|null
     */
    public ?string $undefinedErrorMessage = null;

    /**
     * @var list<MatchConditionMetadata>
     */
    private array $skipWhen = [];

    public function __construct(
        /**
         * Gets property real name.
         *
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * Gets property type info.
         */
        public ?TypeMetadata $type = null,
        ?int $createdAt = null,
    ) {
        $this->alias = $this->name;

        parent::__construct($createdAt);
    }

    /**
     * Dynamically creates AST type representation.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(Context $context): ?TypeStatement
    {
        $info = $this->type;

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

        $name = $this->name;

        if ($context->isDenormalization()) {
            $name = $this->alias;
        }

        return new NamedFieldNode(
            key: $name,
            of: $statement,
            optional: $this->hasDefaultValue(),
        );
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
