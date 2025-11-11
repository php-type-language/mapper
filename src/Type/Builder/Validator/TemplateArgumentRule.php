<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class TemplateArgumentRule implements GroupedRuleInterface
{
    public function getGroup(): string
    {
        return 'template-arg';
    }

    public function validate(TypeStatement $stmt): iterable
    {
        if ($stmt instanceof NamedTypeNode && $stmt->arguments !== null) {
            foreach ($stmt->arguments as $arg) {
                return $this->test($stmt, $arg);
            }
        }

        return [];
    }

    /**
     * @return iterable<array-key, DefinitionException>
     */
    abstract protected function test(NamedTypeNode $stmt, TemplateArgumentNode $arg): iterable;
}
