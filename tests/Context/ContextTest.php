<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Tests\TestCase;

final class ContextTest extends TestCase
{
    public function testContextOfOtherStrictnessSharesEverythingElse(): void
    {
        $context = new Context(new ClassTypes(self::createRepository()));
        $strict = $context->withStrictTypes(true);

        self::assertTrue($strict->config->isStrict);
        self::assertSame($context, $strict->withStrictTypes(false));
        self::assertSame($context->types, $strict->types);
        self::assertSame($context->accessors, $strict->accessors);
        self::assertSame($context->coercers, $strict->coercers);
    }
}
