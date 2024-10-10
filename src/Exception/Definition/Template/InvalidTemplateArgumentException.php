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
     * @var int
     */
    public const CODE_ERROR_INVALID_TEMPLATE_ARGUMENT = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_INVALID_TEMPLATE_ARGUMENT;

    public function __construct(
        private readonly TypeStatement $expected,
        TemplateArgumentNode $argument,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            argument: $argument,
            type: $type,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * Returns the type statement that causes the error.
     *
     * @api
     */
    public function getActualType(): TypeStatement
    {
        return $this->argument->value;
    }

    /**
     * Returns the type statement in which the error occurred.
     *
     * @api
     */
    public function getExpectedType(): TypeStatement
    {
        return $this->expected;
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
            code: self::CODE_ERROR_INVALID_TEMPLATE_ARGUMENT,
            previous: $previous,
        );
    }
}
