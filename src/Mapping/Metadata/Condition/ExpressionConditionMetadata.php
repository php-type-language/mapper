<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Condition;

use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;

final class ExpressionConditionMetadata extends ConditionMetadata
{
    public function __construct(
        public readonly ParsedExpression $expression,
        /**
         * Gets expression variable name.
         *
         * @var non-empty-string
         */
        public readonly string $variable,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    public function match(object $object, mixed $value): bool
    {
        $nodes = $this->expression->getNodes();

        return (bool) $nodes->evaluate([], [
            $this->variable => $object,
        ]);
    }
}
