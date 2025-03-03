<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\Standard\Builder\ClassTypeBuilder;

require __DIR__ . '/../../vendor/autoload.php';

// The set of types and grammar is defined using a "platform". You can create
// your own platform, for example, for a specific DB, or use built-in ones.
//
// For example, let's create a platform that supports only simple types,
// without generics, union types, shapes, and other things.

class SimplePlatform implements PlatformInterface
{
    public function getName(): string
    {
        return 'simple';
    }

    public function getTypes(): iterable
    {
        // The platform will only support objects, that is,
        // references to existing classes.
        yield new ClassTypeBuilder();
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        // Disable all grammar features except the main one.
        return false;
    }
}

class ExampleDTO
{
    public function __construct(
        public readonly int $value = 42,
    ) {}
}

$mapper = new Mapper(new SimplePlatform());

var_dump($mapper->normalize(new ExampleDTO()));
//
// TypeRequiredException: Type "int" for property ExampleDTO::$value
//                        is not defined
//

var_dump($mapper->normalize([new ExampleDTO()], 'array<ExampleDTO>'));
//
// ParseException: Template arguments not allowed in "array<ExampleDTO>" at column 6
//
