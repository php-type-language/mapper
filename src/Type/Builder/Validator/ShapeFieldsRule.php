<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ShapeFieldsRule implements GroupedRuleInterface
{
    public function getGroup(): string
    {
        return 'shape-fields';
    }

    public function validate(TypeStatement $stmt): iterable
    {
        if ($stmt instanceof NamedTypeNode) {
            return $this->test($stmt, $stmt->fields);
        }

        return [];
    }

    /**
     * @return iterable<array-key, DefinitionException>
     */
    abstract protected function test(NamedTypeNode $stmt, ?FieldsListNode $fields): iterable;
}
