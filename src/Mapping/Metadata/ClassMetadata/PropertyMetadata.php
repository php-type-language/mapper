<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Metadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\NamedFieldNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class PropertyMetadata extends Metadata
{
    public function __construct(
        /**
         * Gets property real name.
         *
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * Gets property public name.
         *
         * @var non-empty-string
         */
        public readonly string $alias,
        /**
         * Gets property type information for reading
         */
        public readonly TypeMetadata $read,
        /**
         * Gets property type information for writing
         */
        public readonly TypeMetadata $write,
        /**
         * Contains the default value of the property.
         */
        public readonly ?DefaultValueMetadata $default = null,
        /**
         * Contains a list of rules by which the specified properties
         * are excluded from normalization.
         *
         * @var list<ConditionMetadata>
         */
        public readonly array $skip = [],
        /**
         * An error message that occurs when a property contains
         * an invalid value.
         *
         * @var non-empty-string|null
         */
        public ?string $typeErrorMessage = null,
        /**
         * An error message that occurs when the specified property
         * does not have a value.
         *
         * @var non-empty-string|null
         */
        public ?string $undefinedErrorMessage = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * Dynamically creates AST type representation.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(RuntimeContext $context, bool $read): TypeStatement
    {
        $info = $read ? $this->read : $this->write;

        $statement = clone $info->statement;

        if ($statement instanceof NamedTypeNode) {
            return new NamedTypeNode($statement->name);
        }

        return $statement;
    }

    /**
     * Dynamically creates AST field representation.
     *
     * @codeCoverageIgnore
     */
    public function getFieldNode(RuntimeContext $context, bool $read): NamedFieldNode
    {
        $statement = $this->getTypeStatement($context, $read);

        $name = $this->name;

        if (!$context->direction->isOutput()) {
            $name = $this->alias;
        }

        return new NamedFieldNode(
            key: $name,
            of: $statement,
            optional: $this->default !== null,
        );
    }
}
