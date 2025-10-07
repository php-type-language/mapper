<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Occurs when a type expects a different template argument type than was passed
 */
class InvalidTemplateArgumentException extends TemplateArgumentException
{
    /**
     * Gets the type statement that causes the error.
     */
    public TypeStatement $actual;

    public function __construct(
        /**
         * Gets the type statement in which the error occurred.
         */
        public readonly TypeStatement $expected,
        TemplateArgumentNode $argument,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->actual = $argument->value;

        parent::__construct(
            argument: $argument,
            type: $type,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    public static function becauseTemplateArgumentIsInvalid(
        TypeStatement $expected,
        TemplateArgumentNode $argument,
        TypeStatement $type,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed template argument #{{index}} of type {{type}} must '
            . 'be of type {{expected}}, but {{argument}} given';

        return new self(
            expected: $expected,
            argument: $argument,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
