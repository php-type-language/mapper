<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type requires more template arguments to be specified than required
 */
class TooManyTemplateArgumentsException extends TemplateArgumentsCountException
{
    /**
     * @param int<0, max> $maxArgumentsCount
     */
    public static function becauseHasRedundantArgument(
        int $maxArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self|TemplateArgumentsException {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount > $maxArgumentsCount, new \InvalidArgumentException(
            'Incorrect exception usage',
        ));

        $simplified = self::simplifyException($maxArgumentsCount, $type, $previous);

        if ($simplified !== null) {
            return $simplified;
        }

        $template = 'Type "{{type}}" only accepts {{expectedArgumentsCount}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            expectedArgumentsCount: $maxArgumentsCount,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param int<0, max> $maxArgumentsCount
     */
    private static function simplifyException(
        int $maxArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): ?TemplateArgumentsException {
        if ($maxArgumentsCount <= 0) {
            return TemplateArgumentsNotSupportedException::becauseTooManyArguments($type, $previous);
        }

        return null;
    }
}
