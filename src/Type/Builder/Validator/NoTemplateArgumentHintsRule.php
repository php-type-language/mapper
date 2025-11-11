<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;

class NoTemplateArgumentHintsRule extends TemplateArgumentHintsRule
{
    protected function test(NamedTypeNode $stmt, TemplateArgumentNode $arg): iterable
    {
        if ($arg->hint === null) {
            return;
        }

        yield TemplateArgumentHintsNotSupportedException::becauseTooManyHints(
            argument: $arg,
            type: $stmt,
        );
    }
}
