<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ChildDTO
{
    public function __construct(
        public readonly string $name,
    ) {}
}

class ExampleDTO
{
    public function __construct(
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

var_dump($result);

//
// array:1 [
//   "children" => array:3 [
//     0 => array:1 [
//       "name" => "first"
//     ]
//     1 => array:1 [
//       "name" => "second"
//     ]
//     2 => 42
//   ]
// ]
//
