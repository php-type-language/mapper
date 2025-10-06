<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Condition;

use TypeLang\Mapper\Mapping\Metadata\ConditionInfo;

final class ExpressionConditionInfo extends ConditionInfo
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_CONTEXT_VARIABLE_NAME = 'this';

    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $expression,
        /**
         * @var non-empty-string
         */
        public readonly string $context = self::DEFAULT_CONTEXT_VARIABLE_NAME,
    ) {}
}
