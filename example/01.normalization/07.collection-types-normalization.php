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

class ItemsCollection implements \IteratorAggregate
{
    public function __construct(
        private readonly array $items,
    ) {}

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }
}

$mapper = new Mapper();

$value = new ItemsCollection([
    'key1' => new ItemDTO(value: 'first'),
    'key2' => new ItemDTO(value: 'second'),
    new ItemDTO(value: 'third'),
]);

// Transform collection of ItemDTO to list (array without keys)
// of normalized ItemDTO (key-val array)
$result = $mapper->normalize($value, 'list<ItemDTO>');

var_dump($result);
//
// array:3 [
//   0 => array:1 [
//     "value" => "first"
//   ]
//   1 => array:1 [
//     "value" => "second"
//   ]
//   2 => array:1 [
//     "value" => "third"
//   ]
// ]
//
