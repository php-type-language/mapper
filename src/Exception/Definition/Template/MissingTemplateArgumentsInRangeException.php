<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type requires more template arguments to be specified than required
 *
 * @deprecated TODO
 */
class MissingTemplateArgumentsInRangeException extends TemplateArgumentsInRangeException
{
    /**
     * @param int<0, max> $minSupportedArgumentsCount
     * @param int<1, max> $maxSupportedArgumentsCount
     */
    public static function becauseTemplateArgumentsRequired(
        int $minSupportedArgumentsCount,
        int $maxSupportedArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null
    ): self {
        $template = 'Type "{{type}}" expects at least %s template argument(s), '
            . 'but {{passedArgumentsCount}} were passed';

        $template = $minSupportedArgumentsCount === $maxSupportedArgumentsCount
            ? \sprintf($template, '{{minSupportedArgumentsCount}}')
            : \sprintf($template, 'from {{minSupportedArgumentsCount}} to {{maxSupportedArgumentsCount}}');

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            minSupportedArgumentsCount: $minSupportedArgumentsCount,
            maxSupportedArgumentsCount: $maxSupportedArgumentsCount,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param int<1, max> $maxSupportedArgumentsCount
     */
    public static function becauseNoMoreTemplateArguments(
        int $maxSupportedArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Type "{{type}}" only accepts {{maxSupportedArgumentsCount}} template argument(s), '
            . 'but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            minSupportedArgumentsCount: 0,
            maxSupportedArgumentsCount: $maxSupportedArgumentsCount,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
