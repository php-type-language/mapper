<?php

declare(strict_types=1);

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

$mapper = (new Mapper())
    ->withObjectsAsArrays(false);

$value = new ItemsResultDTO([
    'key1' => new ItemDTO(value: 'first'),
    'key2' => new ItemDTO(value: 'second'),
    new ItemDTO(value: 'third'),
]);

$result = $mapper->normalize($value);

var_dump($result);
//
// object{
//   items: array:3 [
//     "key1" => object{
//       value: "first"
//     }
//     "key2" => object{
//       value: "second"
//     }
//     0 => object{
//       value: "third"
//     }
//   ]
// }
//
