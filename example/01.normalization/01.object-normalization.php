<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        public readonly string $value,
    ) {}
}

$mapper = new Mapper();

$result = $mapper->normalize(new ExampleDTO(value: 'testing'));

var_dump($result);

//
// array:1 [
//   "value" => "testing"
// ]
//
