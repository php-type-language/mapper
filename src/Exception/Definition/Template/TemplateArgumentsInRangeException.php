<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Group of errors related to incorrect number (range) of template arguments
 */
abstract class TemplateArgumentsInRangeException extends TemplateArgumentsException
{
    public function __construct(
        /**
         * @var int<0, max>
         */
        public readonly int $passedArgumentsCount,
        /**
         * @var int<0, max>
         */
        public readonly int $minArgumentsCount,
        /**
         * @var int<0, max>
         */
        public readonly int $maxArgumentsCount,
        NamedTypeNode $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            type: $type,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }
}
