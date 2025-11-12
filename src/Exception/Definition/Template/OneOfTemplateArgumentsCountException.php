<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when the number of arguments does not match one of the required
 */
class OneOfTemplateArgumentsCountException extends TemplateArgumentsException
{
    public function __construct(
        /**
         * @var int<0, max>
         */
        public readonly int $passedArgumentsCount,
        /**
         * @var non-empty-list<int<0, max>>
         */
        public readonly array $expectedArgumentCountVariants,
        NamedTypeNode $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            type: $type,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @param array<array-key, int<0, max>> $variants
     */
    public static function becauseArgumentsCountDoesNotMatch(
        array $variants,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self|TemplateArgumentsException {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert(!\in_array($passedArgumentsCount, $variants, true), new \InvalidArgumentException(
            'Incorrect exception usage',
        ));

        $simplified = self::simplifyException($variants, $type, $previous);

        if ($simplified !== null) {
            return $simplified;
        }

        $template = 'Type "{{type}}" only accepts {{expectedArgumentCountVariants}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        /** @var non-empty-array<array-key, int<0, max>> $variants */
        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            expectedArgumentCountVariants: \array_values($variants),
            type: $type,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param array<array-key, int<0, max>> $variants
     */
    private static function simplifyException(
        array $variants,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): ?TemplateArgumentsException {
        return match (\count($variants)) {
            0 => TemplateArgumentsNotSupportedException::becauseTooManyArguments($type),
            1 => ($expectedArgumentsCount = \reset($variants)) < ($type->arguments?->count() ?? 0)
                ? TooManyTemplateArgumentsException::becauseHasRedundantArgument($expectedArgumentsCount, $type, $previous)
                : MissingTemplateArgumentsException::becauseNoRequiredArgument($expectedArgumentsCount, $type, $previous),
            default => null,
        };
    }
}
