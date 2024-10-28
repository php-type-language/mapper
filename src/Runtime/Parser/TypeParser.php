<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly TypeParserRuntimeInterface $runtime,
    ) {}

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        return $this->runtime->getStatementByDefinition($definition);
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        // @phpstan-ignore-next-line : The "get_debug_type" function always return a non-empty-string
        return $this->getStatementByDefinition(\get_debug_type($value));
    }
}
