<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Attribute\MapProperty;

require __DIR__ . '/../vendor/autoload.php';

// The attribute driver is used to specify default types. To specify a specific
// type, just add the #[MapProperty] attribute.
//
// If the types do not match, an appropriate error will be thrown.

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

$result = $mapper->normalize(new ExampleDTO(
    children: [
        new ChildDTO('first'),
        new ChildDTO('second'),
        42,
    ]
));

//
// InvalidValueException: Passed value must be of type ChildDTO{name: string},
//                        but int given in $.children[2]
//
