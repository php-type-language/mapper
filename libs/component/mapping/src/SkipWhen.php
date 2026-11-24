<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use JetBrains\PhpStorm\Language;

/**
 * The property is left out of the normalized data in case of the expression
 * of `symfony/expression-language` over the object and the value of the
 * property (`value`) is true. The expression is parsed when the metadata is
 * read, its functions are the functions of the language of the mapper.
 *
 * ```
 * #[SkipWhen('this.isPrivate() or value === ""')]
 * public string $email;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class SkipWhen
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        #[Language('JavaScript')]
        public readonly string $expr,
        /**
         * Name of the object in the expression
         *
         * @var non-empty-string
         */
        public readonly string $context = 'this',
    ) {}
}
