<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Context\Coercers;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Mapping\MetadataProviderFactory;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Printer\PrettyTypePrinter;

final class ContextTest extends TestCase
{
    public function testContextOfOtherStrictnessSharesEverythingElse(): void
    {
        $accessors = new ClassAccessors(
            properties: PropertyAccessorFactory::createDefault(),
            instantiators: InstantiatorFactory::createDefault(),
            hooks: [],
        );
        $repository = new TypeRepository(
            builders: [],
            metadata: MetadataProviderFactory::create(),
            decorators: [],
            accessors: $accessors,
            printer: new PrettyTypePrinter(),
        );
        $context = new Context(
            types: new ClassTypes($repository),
            config: new Configuration(),
            accessors: $accessors,
            coercers: Coercers::createDefault(),
        );
        $strict = $context->withStrict(true);

        self::assertTrue($strict->config->strict);
        self::assertSame($context, $strict->withStrict(false));
        self::assertSame($context->types, $strict->types);
        self::assertSame($context->accessors, $strict->accessors);
        self::assertSame($context->coercers, $strict->coercers);
    }
}
