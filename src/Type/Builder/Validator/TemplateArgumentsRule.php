<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class TemplateArgumentsRule implements GroupedRuleInterface
{
    public function getGroup(): string
    {
        return 'template-args';
    }

    public function validate(TypeStatement $stmt): iterable
    {
        if ($stmt instanceof NamedTypeNode) {
            return $this->test($stmt, $stmt->arguments);
        }

        return [];
    }

    /**
     * @return iterable<array-key, DefinitionException>
     */
    abstract protected function test(NamedTypeNode $stmt, ?TemplateArgumentsListNode $args): iterable;
}
