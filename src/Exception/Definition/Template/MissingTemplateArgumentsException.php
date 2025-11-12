<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type requires more template arguments to be specified than required
 */
class MissingTemplateArgumentsException extends TemplateArgumentsCountException
{
    public const ERROR_CODE_COUNT = 0x01;
    public const ERROR_CODE_GREATER_THAN = 0x02;

    /**
     * @param int<0, max> $minArgumentsCount
     */
    public static function becauseArgumentsCountRequired(
        int $minArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount < $minArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed type`s argument count should be less than min bound',
        ));

        $template = 'Type "{{type}}" expects at least {{expectedArgumentsCount}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            expectedArgumentsCount: $minArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_COUNT,
            previous: $previous,
        );
    }

    /**
     * @param int<0, max> $minArgumentsCount
     */
    public static function becauseArgumentsCountLessThan(
        int $minArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount > $minArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed type`s argument count should be less than min bound',
        ));

        $template = 'Type "{{type}}" accepts no more than {{expectedArgumentsCount}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            expectedArgumentsCount: $minArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_GREATER_THAN,
            previous: $previous,
        );
    }
}
