<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Mapping\Metadata\ExpressionMetadata;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SkipWhen
{
    public function __construct(
        /**
         * Contains an expression for which the field will be skipped
         * during normalization.
         *
         * Requires "`symfony/expression-language`" package to be installed.
         *
         * @link https://symfony.com/doc/current/components/expression_language.html
         *
         * @var non-empty-string
         */
        #[Language('JavaScript')]
        public readonly string $expr,
        /**
         * Contains the name of the variable that will hold the reference
         * of the object.
         *
         * @var non-empty-string
         */
        public readonly string $context = ExpressionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME,
    ) {}
}
