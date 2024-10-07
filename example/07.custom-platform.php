<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Registry\Registry;

require __DIR__ . '/../vendor/autoload.php';

// The set of types and grammar is defined using a "platform". You can create
// your own platform, for example, for a specific DB, or use built-in ones.
//
// For example, let's create a platform that supports only simple types,
// without generics, union types, shapes, and other things.

class SimplePlatform implements \TypeLang\Mapper\Platform\PlatformInterface
{
    public function getName(): string
    {
        return 'simple';
    }

    public function getTypes(): iterable
    {
        // The platform will only support objects, that is,
        // references to existing classes.
        yield new \TypeLang\Mapper\Type\Builder\ObjectTypeBuilder();
    }

    public function isFeatureSupported(\TypeLang\Mapper\Platform\GrammarFeature $feature): bool
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

$platform = new SimplePlatform();

$mapper = new Mapper(new Registry($platform));

var_dump($mapper->normalize(new ExampleDTO()));
//
// TypeRequiredException: The ExampleDTO::$value property contains an
//                        unregistered type that cannot be explicitly converted
//

var_dump($mapper->normalize([new ExampleDTO()], 'array<ExampleDTO>'));
//
// ParseException: Template arguments not allowed in "array<ExampleDTO>" at column 6
//
