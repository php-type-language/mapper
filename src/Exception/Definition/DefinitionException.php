<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Mapper\Exception\Template;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Occurs when the type was incorrectly defined.
 */
abstract class DefinitionException extends \InvalidArgumentException implements MapperExceptionInterface
{
    public readonly Template $template;

    public function __construct(
        protected readonly TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($template, $code, $previous);

        /** @phpstan-ignore-next-line : Stringable is allowed to set in "message" */
        $this->message = $this->template = new Template($template, $this);
    }

    /**
     * Returns the type statement whose definition caused the error.
     *
     * @api
     */
    public function getType(): TypeStatement
    {
        return $this->type;
    }
}
