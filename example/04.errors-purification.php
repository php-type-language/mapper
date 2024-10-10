<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\MappingException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapProperty;
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


$payload = new ExampleDTO([
    new ChildDTO('first'),
    new ChildDTO('second'),
    42,
]);

try {
    $result = $mapper->normalize($payload);
} catch (MappingException $e) {
    // Replace all internal type names with common "object" keyword.
    throw $e->explain(function (Name $name): ?Name {
        if (\class_exists($name->toString())) {
            return new Name('object');
        }

        return null;
    });

    //
    // InvalidValueException: Passed value must be of type object{name: string},
    //                        but int ("42") given at $.children[2]
    //
}
