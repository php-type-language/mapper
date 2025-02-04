<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Introspection;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\Shape\NamedFieldNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class ClassIntrospection implements IntrospectionInterface
{
    public function __construct(
        /**
         * @var ClassMetadata<object>
         */
        private readonly ClassMetadata $metadata,
    ) {}

    public function getTypeStatement(Context $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->metadata->getName());
        }

        $fields = [];

        foreach ($this->metadata->getProperties() as $property) {
            $fields[] = $this->getFieldNode($property, $context);
        }

        if ($fields === []) {
            return new NamedTypeNode($this->metadata->getName());
        }

        return new NamedTypeNode($this->metadata->getName(), fields: new FieldsListNode($fields));
    }

    private function getFieldNode(PropertyMetadata $metadata, Context $context): FieldNode
    {
        $name = $metadata->getName();

        if ($context->isDenormalization()) {
            $name = $metadata->getExportName();
        }

        return new NamedFieldNode(
            key: $name,
            of: $metadata->getTypeStatement($context),
            optional: $metadata->hasDefaultValue(),
        );
    }
}
