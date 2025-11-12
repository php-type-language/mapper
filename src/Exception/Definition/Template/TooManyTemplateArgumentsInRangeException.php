<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type supports fewer arguments than were passed
 */
class TooManyTemplateArgumentsInRangeException extends TemplateArgumentsInRangeException
{
    public const ERROR_CODE_IN_RANGE = 0x01;

    /**
     * @param int<0, max> $minArgumentsCount
     * @param int<1, max> $maxArgumentsCount
     */
    public static function becauseHasRedundantArgument(
        int $minArgumentsCount,
        int $maxArgumentsCount,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self|TemplateArgumentsException {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($maxArgumentsCount > $minArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed max bound must be greater than min bound',
        ));

        assert($passedArgumentsCount > $maxArgumentsCount, new \InvalidArgumentException(
            'Semantic Violation: Passed type`s argument count should be greater than max bound',
        ));

        $template = 'Type "{{type}}" only accepts from {{minArgumentsCount}}'
            . ' to {{maxArgumentsCount}} template argument(s),'
            . ' but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            minArgumentsCount: $minArgumentsCount,
            maxArgumentsCount: $maxArgumentsCount,
            type: $type,
            template: $template,
            code: self::ERROR_CODE_IN_RANGE,
            previous: $previous,
        );
    }
}
