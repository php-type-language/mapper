<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\SkipCondition;

use Symfony\Component\ExpressionLanguage\ParsedExpression;

/**
 * Expression of `symfony/expression-language` over the object and the value
 * of the property, parsed when the metadata is read. The functions of the
 * expression are not stored: the mapper evaluates it by its own language,
 * evaluating it here is possible for an expression without functions.
 *
 * ```
 * #[SkipWhen('this.isPrivate() or value === ""')]
 * public string $email;
 * ```
 */
final class ExpressionSkipCondition implements SkipConditionInterface
{
    /**
     * Name of the value of the property in the expression
     */
    public const VALUE = 'value';

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
        return (bool) $this->expression->getNodes()->evaluate([], [
            self::VALUE => $value,
            $this->variable => $object,
        ]);
    }
}
