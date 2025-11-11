<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;

class NoTemplateArgumentsRule extends TemplateArgumentsRule
{
    protected function test(NamedTypeNode $stmt, ?TemplateArgumentsListNode $args): iterable
    {
        if ($args === null) {
            return null;
        }

        yield TemplateArgumentsNotSupportedException::becauseTooManyArguments($stmt);
    }
}
