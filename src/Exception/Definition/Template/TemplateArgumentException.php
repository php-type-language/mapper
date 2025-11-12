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
        $this->index = $this->getArgumentIndex($type, $argument);

        parent::__construct($type, $template, $code, $previous);
    }

    private function getArgumentIndex(NamedTypeNode $type, TemplateArgumentNode $argument): int
    {
        $index = $type->arguments?->findIndex($argument);

        assert($index !== null, new \InvalidArgumentException(
            'Template argument is not a part of passed type',
        ));

        return $index + 1;
    }
}
