<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use TypeLang\Parser\Node\Literal\BoolLiteralNode;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeParserFacade implements TypeParserFacadeInterface
{
    public function __construct(
        private readonly TypeParserInterface $runtime,
    ) {}

    public function getStatementByDefinition(string $definition): TypeStatement
    {
        return $this->runtime->getStatementByDefinition($definition);
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        return match (true) {
            $value === null => new NullLiteralNode(),
            \is_bool($value) => new BoolLiteralNode($value),
            // @phpstan-ignore-next-line : The "get_debug_type" function always return a non-empty-string
            default => $this->getStatementByDefinition(\get_debug_type($value)),
        };
    }
}
