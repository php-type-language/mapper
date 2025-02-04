<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Introspection;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class PropertyIntrospection implements IntrospectionInterface
{
    public function __construct(
        private readonly PropertyMetadata $metadata,
    ) {}

    public function getTypeStatement(Context $context): TypeStatement
    {
        $info = $this->metadata->findTypeInfo();

        if ($info === null) {
            return new NamedTypeNode('mixed');
        }

        $statement = clone $info->getTypeStatement();

        if ($context->isDetailedTypes() || !$statement instanceof NamedTypeNode) {
            return $statement;
        }

        return new NamedTypeNode($statement->name);
    }
}
