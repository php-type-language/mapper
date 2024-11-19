<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * An exception associated with ONE specific template argument.
 */
abstract class TemplateArgumentException extends TemplateArgumentsException
{
    /**
     * @var int<0, max>
     */
    protected readonly int $index;

    public function __construct(
        protected readonly TemplateArgumentNode $argument,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->index = self::fetchArgumentIndex($argument, $type);

        parent::__construct($type, $template, $code, $previous);
    }

    /**
     * @return int<0, max>
     */
    private static function fetchArgumentIndex(TemplateArgumentNode $argument, TypeStatement $type): int
    {
        $index = 0;

        if (!$type instanceof NamedTypeNode) {
            return $index;
        }

        foreach ($type->arguments ?? [] as $actual) {
            if ($actual === $argument) {
                return $index + 1;
            }

            ++$index;
        }

        return $index;
    }

    /**
     * @api
     */
    public function getArgument(): TemplateArgumentNode
    {
        return $this->argument;
    }
}
