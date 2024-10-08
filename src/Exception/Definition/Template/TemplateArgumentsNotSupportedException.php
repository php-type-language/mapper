<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class TemplateArgumentsNotSupportedException extends TemplateArgumentsCountException
{
    /**
     * @var int
     */
    public const CODE_ERROR_TEMPLATE_ARGUMENTS_NOT_SUPPORTED = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_TEMPLATE_ARGUMENTS_NOT_SUPPORTED;

    /**
     * @param int<0, max> $passedArgumentsCount
     */
    public static function becauseTemplateArgumentsNotSupported(
        int $passedArgumentsCount,
        TypeStatement $type,
        ?\Throwable $previous = null
    ): self {
        $template = 'Type "{{type}}" does not support template arguments, '
            . 'but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            minSupportedArgumentsCount: 0,
            maxSupportedArgumentsCount: 0,
            type: $type,
            template: $template,
            code: self::CODE_ERROR_TEMPLATE_ARGUMENTS_NOT_SUPPORTED,
            previous: $previous,
        );
    }
}
