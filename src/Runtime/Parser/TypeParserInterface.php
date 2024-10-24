<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeParserInterface
{
    /**
     * TODO in the future specific local exception types for the parse errors
     *      should be added.
     *
     * @param non-empty-string $type
     *
     * @throws \Throwable in case of any internal error occurs
     */
    public function getStatementByType(#[Language('PHP')] string $type): TypeStatement;

    /**
     * TODO in the future specific local exception types for the parse errors
     *      should be added.
     *
     * @throws \Throwable in case of any internal error occurs
     */
    public function getStatementByValue(mixed $value): TypeStatement;
}
