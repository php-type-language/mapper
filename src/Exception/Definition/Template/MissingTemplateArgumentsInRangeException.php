<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type requires more template arguments to be specified than required
 */
class MissingTemplateArgumentsInRangeException extends TemplateArgumentsInRangeException
{
    /**
     * @param int<0, max> $minArgumentsCount
     * @param int<1, max> $maxArgumentsCount
     */
    public static function becauseNoRequiredArgument(
        int $minArgumentsCount,
        int $maxArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($maxArgumentsCount > $minArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed max bound must be greater than min bound',
        ));

        assert($passedArgumentsCount < $minArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed type`s argument count should be greater than min bound',
        ));

        $template = 'Type "{{type}}" expects at least from {{minArgumentsCount}}'
            . ' to {{maxArgumentsCount}} template argument(s),'
            . ' but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            minArgumentsCount: $minArgumentsCount,
            maxArgumentsCount: $maxArgumentsCount,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
