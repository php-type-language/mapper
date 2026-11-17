<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\SkipCondition;

use Symfony\Component\ExpressionLanguage\ParsedExpression;

/**
 * Expression of `symfony/expression-language` over the object, parsed when
 * the metadata is read: evaluating it needs no expression language.
 *
 * ```
 * #[SkipWhen('this.isPrivate()')]
 * public string $email;
 * ```
 */
final class ExpressionSkipCondition implements SkipConditionInterface
{
    public function __construct(
        public readonly ParsedExpression $expression,
        /**
         * Name of the object in the expression
         *
         * @var non-empty-string
         */
        public readonly string $variable = 'this',
    ) {}

    public function isMatched(object $object, mixed $value): bool
    {
        return (bool) $this->expression->getNodes()->evaluate([], [$this->variable => $object]);
    }
}
