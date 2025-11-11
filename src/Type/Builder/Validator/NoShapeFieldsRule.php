<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;

class NoShapeFieldsRule extends ShapeFieldsRule
{
    protected function test(NamedTypeNode $stmt, ?FieldsListNode $fields): iterable
    {
        if ($fields === null) {
            return;
        }

        yield ShapeFieldsNotSupportedException::becauseTooManyShapeFields($stmt);
    }
}
