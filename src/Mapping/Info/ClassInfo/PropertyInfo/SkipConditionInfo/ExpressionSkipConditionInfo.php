<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info;

use TypeLang\Mapper\Mapping\Info\ClassInfo\PropertyInfo\SkipConditionInfo;

final class ExpressionSkipConditionInfo extends SkipConditionInfo
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
