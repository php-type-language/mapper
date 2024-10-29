<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use Symfony\Component\ExpressionLanguage\ParsedExpression;

final class ExpressionMetadata extends Metadata
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_CONTEXT_VARIABLE_NAME = 'this';

    /**
     * @param non-empty-string $context
     */
    public function __construct(
        private readonly ParsedExpression $expression,
        private readonly string $context = self::DEFAULT_CONTEXT_VARIABLE_NAME,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getContextVariableName(): string
    {
        return $this->context;
    }

    /**
     * @api
     */
    public function getExpression(): ParsedExpression
    {
        return $this->expression;
    }
}
