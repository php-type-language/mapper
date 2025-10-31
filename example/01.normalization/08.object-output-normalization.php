<?php

declare(strict_types=1);

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ItemDTO
{
    public function __construct(
        public readonly string $value,
    ) {}
}

class ItemsResultDTO
{
    public function __construct(
        public readonly array $items,
    ) {}
}

$mapper = new Mapper(
    config: new Configuration(
        isObjectAsArray: true,
    ),
);

$value = new ItemsResultDTO([
    'key1' => new ItemDTO(value: 'first'),
    'key2' => new ItemDTO(value: 'second'),
    new ItemDTO(value: 'third'),
]);

$result = $mapper->normalize($value);

var_dump($result);
//
// array:1 [
//   "items" => array:3 [
//     "key1" => array:1 [
//       "value" => "first"
//     ]
//     "key2" => array:1 [
//       "value" => "second"
//     ]
//     0 => array:1 [
//       "value" => "third"
//     ]
//   ]
// ]
//
