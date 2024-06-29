<?php

declare(strict_types=1);

use TypeLang\Mapper\Attribute\MapProperty;
use TypeLang\Mapper\Exception\Mapping\MappingException;
use TypeLang\Mapper\Mapper;
use TypeLang\Parser\Node\Name;

require __DIR__ . '/../vendor/autoload.php';

// In some cases, you don't want to display internal information about objects
// as is, because this information is sensitive.
//
// In this case, you can transform the type names by eliminating them and
// replacing them with generic (i.e. "object" or "enum") names.

class ChildDTO
{
    public function __construct(
        public readonly string $name,
    ) {}
}

class ExampleDTO
{
    public function __construct(
        #[MapProperty('list<ChildDTO>')]
        public readonly array $children = [],
    ) {}
}

$mapper = new Mapper();

// Function to replace all internal type names with generic ones.
$purifier = static function (Name $name): ?Name {
    return match (true) {
        \class_exists($name->toString()) => new Name('object'),
        \enum_exists($name->toString()) => new Name('enum'),
        default => $name,
    };
};

$payload = new ExampleDTO([
    new ChildDTO('first'),
    new ChildDTO('second'),
    42,
]);

try {
    $result = $mapper->normalize($payload);
} catch (MappingException $e) {
    // Replace all internal type names with generic ones.
    throw $e->explain($purifier);

    //
    // InvalidValueException: Passed value must be of type object{name: string},
    //                        but int given at "children.2"
    //
}



// In addition, you can also completely change the formatting of types. This
// feature allows you to completely control how the type looks when printed
// to a string.

try {
    $result = $mapper->normalize($payload);
} catch (MappingException $e) {
    throw $e
        // Use native PHP types instead of TypeLang types.
        ->setTypePrinter(new \TypeLang\Printer\NativeTypePrinter())
        // Replace all internal type names with generic ones.
        ->explain($purifier);

    //
    // InvalidValueException: Passed value must be of type object,
    //                        but int given at "children.2"
    //
}
