<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type requires more template arguments to be specified than required
 */
class TooManyTemplateArgumentsException extends TemplateArgumentsCountException
{
    public const ERROR_CODE_COUNT = 0x01;
    public const ERROR_CODE_LESS_THAN = 0x02;

    /**
     * @param int<0, max> $expectedArgumentsCount
     */
    public static function becauseArgumentsCountRequired(
        int $expectedArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount > $expectedArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed type`s argument count should be greater than expected',
        ));

        $template = 'Type "{{type}}" only accepts {{expectedArgumentsCount}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            expectedArgumentsCount: $expectedArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_COUNT,
            previous: $previous,
        );
    }

    /**
     * @param int<0, max> $maxArgumentsCount
     */
    public static function becauseArgumentsCountLessThan(
        int $maxArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount < $maxArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed type`s argument count should be greater than max bound',
        ));

        $template = 'Type "{{type}}" accepts at least {{expectedArgumentsCount}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            expectedArgumentsCount: $maxArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_LESS_THAN,
            previous: $previous,
        );
    }
}
