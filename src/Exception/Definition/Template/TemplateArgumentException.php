<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;

/**
 * An exception associated with ONE specific template argument.
 */
abstract class TemplateArgumentException extends TemplateArgumentsException
{
    /**
     * @var int<0, max>
     */
    public readonly int $index;

    public function __construct(
        public readonly TemplateArgumentNode $argument,
        NamedTypeNode $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->index = self::getArgumentIndex($argument, $type);

        parent::__construct($type, $template, $code, $previous);
    }

    /**
     * @return int<0, max>
     */
    private static function getArgumentIndex(TemplateArgumentNode $argument, NamedTypeNode $type): int
    {
        $index = 0;

        if ($type->arguments === null) {
            return $index;
        }

        foreach ($type->arguments as $actual) {
            if ($actual === $argument) {
                return $index + 1;
            }

            ++$index;
        }

        return $index;
    }
}
