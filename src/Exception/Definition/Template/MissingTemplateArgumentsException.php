<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Occurs when a type requires more template arguments to be specified than required
 */
class MissingTemplateArgumentsException extends TemplateArgumentsCountException
{
    /**
     * @var int
     */
    public const CODE_ERROR_MISSING_TEMPLATE_ARGUMENTS = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_MISSING_TEMPLATE_ARGUMENTS;

    /**
     * @param int<0, max> $passedArgumentsCount
     * @param int<0, max> $minSupportedArgumentsCount
     * @param int<0, max> $maxSupportedArgumentsCount
     */
    public static function becauseTemplateArgumentsRangeRequired(
        int $passedArgumentsCount,
        int $minSupportedArgumentsCount,
        int $maxSupportedArgumentsCount,
        TypeStatement $type,
        ?\Throwable $previous = null
    ): self {
        $template = 'Type "{{type}}" expects at least %s template argument(s), '
            . 'but {{passedArgumentsCount}} were passed';

        $template = $minSupportedArgumentsCount === $maxSupportedArgumentsCount
            ? \sprintf($template, '{{minSupportedArgumentsCount}}')
            : \sprintf($template, 'from {{minSupportedArgumentsCount}} to {{maxSupportedArgumentsCount}}');

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            minSupportedArgumentsCount: $minSupportedArgumentsCount,
            maxSupportedArgumentsCount: $maxSupportedArgumentsCount,
            type: $type,
            template: $template,
            code: self::CODE_ERROR_MISSING_TEMPLATE_ARGUMENTS,
            previous: $previous,
        );
    }
}
