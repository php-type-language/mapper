<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use Symfony\Component\ExpressionLanguage\ParsedExpression;

final class ExpressionConditionMetadata extends MatchConditionMetadata
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_CONTEXT_VARIABLE_NAME = 'this';

    /**
     * @param non-empty-string $variable
     */
    public function __construct(
        public readonly ParsedExpression $expression,
        /**
         * Gets expression variable name.
         *
         * @var non-empty-string
         */
        public readonly string $variable = self::DEFAULT_CONTEXT_VARIABLE_NAME,
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
