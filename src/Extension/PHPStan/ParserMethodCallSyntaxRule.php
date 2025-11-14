<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan;

use TypeLang\Mapper\Extension\PHPStan\MethodCallSyntaxCheckRule\MethodCallTarget;
use TypeLang\Parser\ParserInterface;

final class ParserMethodCallSyntaxRule extends MethodCallSyntaxCheckRule
{
    /**
     * @return \Traversable<array-key, MethodCallTarget>
     */
    protected function createAnalyzedDeclarations(): \Traversable
    {
        yield new MethodCallTarget(ParserInterface::class, 'parse', 0);
    }
}
