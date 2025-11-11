<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator\Traverser;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Type\Builder\Validator\RuleInterface;
use TypeLang\Parser\Node\Node;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Traverser\Command;
use TypeLang\Parser\Traverser\Visitor;

final class ErrorCollectorVisitor extends Visitor
{
    /**
     * @var list<DefinitionException>
     */
    protected array $errors = [];

    public function __construct(
        /**
         * @var list<RuleInterface>
         */
        private readonly array $rules,
    ) {}

    /**
     * @return list<DefinitionException>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    #[\Override]
    public function before(): void
    {
        $this->errors = [];
    }

    public function enter(Node $node): ?Command
    {
        if (!$node instanceof TypeStatement) {
            return null;
        }

        foreach ($this->rules as $rule) {
            foreach ($rule->validate($node) as $error) {
                $this->errors[] = $error;
            }
        }

        return null;
    }
}
