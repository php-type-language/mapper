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
     * @param non-empty-array<array-key, int<0, max>> $variants
     */
    public static function becauseArgumentsCountDoesNotMatch(
        array $variants,
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        /** @phpstan-ignore-next-line : Additional DbC precondition */
        assert(\count($variants) !== 0, new \InvalidArgumentException(
            'Semantic Violation: Argument variants should be greater than 0',
        ));

        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert(!\in_array($passedArgumentsCount, $variants, true), new \InvalidArgumentException(
            'Semantic Violation: Passed arguments count should not be in variants',
        ));

        $template = 'Type "{{type}}" only accepts {{expectedArgumentCountVariants}}'
            . ' template argument(s), but {{passedArgumentsCount}} were passed';

        /** @var non-empty-array<array-key, int<0, max>> $variants */
        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            expectedArgumentCountVariants: \array_values($variants),
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
