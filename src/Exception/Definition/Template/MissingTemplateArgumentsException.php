<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type requires more template arguments to be specified than required
 */
class MissingTemplateArgumentsException extends TemplateArgumentsCountException
{
    /**
     * @param int<0, max> $minArgumentsCount
     */
    public static function becauseNoRequiredArgument(
        int $minArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount < $minArgumentsCount, new \InvalidArgumentException(
            'Incorrect exception usage',
        ));

        $template = 'Type "{{type}}" expects at least {{expectedArgumentsCount}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            expectedArgumentsCount: $minArgumentsCount,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
