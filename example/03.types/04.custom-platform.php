<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Provider\MetadataReaderProvider;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Type\Builder\ClassFromArrayTypeBuilder;
use TypeLang\Mapper\Type\Builder\ClassToArrayTypeBuilder;

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

    public function getTypes(Direction $direction): iterable
    {
        $driver = new MetadataReaderProvider(new AttributeReader());

        // The platform will only support objects, that is,
        // references to existing classes.
        if ($direction === Direction::Normalize) {
            yield new ClassToArrayTypeBuilder($driver);
        } else {
            yield new ClassFromArrayTypeBuilder($driver);
        }
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

try {
    var_dump($mapper->normalize(new ExampleDTO()));
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
}
//
// TypeRequiredException: Type "int" for property ExampleDTO::$value
//                        is not defined
//

try {
    var_dump($mapper->normalize([new ExampleDTO()], 'array<ExampleDTO>'));
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
}
//
// ParseException: Template arguments not allowed in "array<ExampleDTO>"
//                 at column 6
//
