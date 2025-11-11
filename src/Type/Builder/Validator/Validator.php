<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Type\Builder\Validator\Traverser\ErrorCollectorVisitor;
use TypeLang\Mapper\Type\Builder\Validator\Traverser\RuleCollector;
use TypeLang\Parser\Node\Statement;
use TypeLang\Parser\Traverser;

final class Validator implements RuleInterface
{
    private readonly ErrorCollectorVisitor $visitor;

    /**
     * @param iterable<mixed, RuleInterface> $rules
     */
    public function __construct(iterable $rules = [])
    {
        $this->visitor = new ErrorCollectorVisitor(
            rules: RuleCollector::collect(
                rules: $this->composeRules($rules)
            ),
        );
    }

    /**
     * @param iterable<mixed, RuleInterface> $extra
     * @return iterable<mixed, RuleInterface>
     */
    private function composeRules(iterable $extra): iterable
    {
        yield new NoTemplateArgumentsRule();
        yield new NoTemplateArgumentHintsRule();
        yield new NoShapeFieldsRule();

        yield from $extra;
    }

    public function validate(Statement $stmt): iterable
    {
        (new Traverser([$this->visitor]))
            ->traverse([$stmt]);

        return $this->visitor->getErrors();
    }
}
