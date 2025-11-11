<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type supports fewer arguments than were passed
 *
 * @deprecated TODO
 */
class TooManyTemplateArgumentsInRangeException extends TemplateArgumentsInRangeException
{
    public const ERROR_CODE_IN_RANGE = 0x01;
    public const ERROR_CODE_NO_MORE_THAN = 0x02;

    /**
     * @param int<0, max> $minSupportedArgumentsCount
     * @param int<1, max> $maxSupportedArgumentsCount
     */
    public static function becauseTooManyThanRangeTemplateArguments(
        int $minSupportedArgumentsCount,
        int $maxSupportedArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        if ($minSupportedArgumentsCount === 0 || $minSupportedArgumentsCount === $maxSupportedArgumentsCount) {
            return self::becauseTooManyTemplateArguments($maxSupportedArgumentsCount, $type, $previous);
        }

        if ($maxSupportedArgumentsCount < $minSupportedArgumentsCount) {
            [$minSupportedArgumentsCount, $maxSupportedArgumentsCount]
                = [$maxSupportedArgumentsCount, $minSupportedArgumentsCount];
        }

        $template = 'Type "{{type}}" only accepts from {{minSupportedArgumentsCount}}'
            . ' to {{maxSupportedArgumentsCount}} template argument(s),'
            . ' but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            minSupportedArgumentsCount: $minSupportedArgumentsCount,
            maxSupportedArgumentsCount: $maxSupportedArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_IN_RANGE,
            previous: $previous,
        );
    }

    /**
     * @param int<1, max> $requiredArgumentsCount
     */
    public static function becauseTooManyTemplateArguments(
        int $requiredArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Type "{{type}}" only accepts {{maxSupportedArgumentsCount}} template argument(s), '
            . 'but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            minSupportedArgumentsCount: 0,
            maxSupportedArgumentsCount: $requiredArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_NO_MORE_THAN,
            previous: $previous,
        );
    }
}
